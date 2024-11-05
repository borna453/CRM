<?php

namespace App\Filament\Resources;

use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers\CallEventsRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\CompanyUsersRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\OpportunitiesRelationManager;
use App\Livewire\CompanyCard;
use App\Filament\Resources\CompanyResource\RelationManagers\PinboardItemsRelationManager;
use App\Models\Company;
use App\Models\User;
use App\Utils\Filament\Actions\RestoreActionHelper;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

use Illuminate\Validation\Rules\Unique;
use SaloonKvk\Integrations\KvkApi\Requests\KvkHandelsRegisterRequest;
use SaloonKvk\Integrations\KvkApi\Requests\KvkBasisProfielRequest;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $slug = 'companies';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 9;

    protected static function getBasicProfile(string $kvkNumber): ?array
    {
        $response = (new KvkBasisProfielRequest($kvkNumber))->send();
        return $response->dto();
    }

    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getConditionalSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Company::query()->withCount('tasks')->withCount('opportunities');
            })
            ->columns([
                BadgeableColumn::make('name')
                    ->label(__('portal.companies.name'))
                    ->searchable()
                    ->sortable()
                    ->suffixBadges([
                        Badge::make(__('portal.admin'))->color('primary')->visible(fn($record) => $record->is_main),
                    ]),
                TextColumn::make('open_tasks')
                    ->label(__('portal.tasks.open_tasks'))
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        return $record->tasks()->open()->count();
                    })
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('open_opportunities')
                    ->label(__('portal.opportunities.open'))
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        return $record->opportunities()->open()->count();
                    })
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()->visible(fn($record) => !$record->deleted_at),
                RestoreActionHelper::restoreAction()->hidden(fn() => !auth()->user()->can('restore', Company::class)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(function (Company $record) {
                if($record->trashed()){
                    return null;
                }
                return CompanyResource::getUrl('view', ['record' => $record]);
            })
            ->emptyStateHeading(__('portal.companies.no_companies'));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Grid::make(10)->schema([
                    Livewire::make(CompanyCard::class, ['company' => $infolist->record])->columnSpan(6)
                ])
            ]);
    }

    protected static function getConditionalSchema(): array
    {
        return [
            Grid::make()
                ->schema(function ($context) {
                    if ($context === 'create') {
                        return static::getWizardSchema();
                    }

                    return static::getFormSchema();
                })
                ->columns(1)
        ];
    }

    public static function getFormSchema($view = null): array
    {
        return [
            TextInput::make('coc_number')
                ->label(__('portal.companies.coc_number'))
                ->extraAttributes(['wire:keydown.enter' => empty($view) ? 'handleEnterKey' : null])
                ->suffixAction(Action::make('get_details')
                    ->icon('heroicon-o-document-magnifying-glass')->action(function ($get, $state, $set) {
                    if (strlen($state) >= 8) {
                        $response = (new KvkHandelsRegisterRequest(
                            kvkNummer: is_numeric($state) ? $state : null,
                            companyName: is_numeric($state) ? null : $state
                        ))->send();
                        $data = $response->dto();

                        if (!empty($data) && count($data) > 1) {
                            $dataArray = array_map(function ($dto) {
                                return [
                                    'kvkNummer' => $dto->kvkNummer ?? '',
                                    'vestigingsnummer' => $dto->vestigingsnummer ?? '',
                                    'naam' => $dto->naam ?? '',
                                    'address' => ($dto->straatnaam ?? '') . ' ' . ($dto->huisnummer ?? ''),
                                    'postcode' => $dto->postcode ?? '',
                                    'plaats' => $dto->plaats ?? '',
                                    'land' => $dto->land ?? '',
                                    'type' => $dto->type ?? '',
                                ];
                            }, $data);
                            $set('coc_data', json_encode($dataArray));

                            if (count($dataArray) === 1) {
                                $selectedItem = $dataArray[0];
                                $set('name', $selectedItem['naam']);
                                $set('address', $selectedItem['address']);
                                $set('zip_code', $selectedItem['postcode']);
                                $set('city', $selectedItem['plaats']);
                                $set('should_show_select', false);
                            } else {
                                $set('should_show_select', true);
                            }
                        } else {
                            return Notification::make()
                                ->title('Error')
                                ->body(__('portal.companies.coc_number_not_found'))
                                ->danger()
                                ->send();
                        }
                    } else {
                        return Notification::make()
                            ->title('Error')
                            ->body(__('portal.companies.coc_number_too_short'))
                            ->danger()
                            ->send();
                    }
                })),

            Hidden::make('should_show_select')->default(false)->dehydrated(false),

            Select::make('choose')
                ->label(__('portal.companies.choose'))
                ->options(function ($get) {
                    $cocData = json_decode($get('coc_data'), true);
                    if (is_array($cocData)) {
                        return collect($cocData)->mapWithKeys(function ($item) {
                            return ["{$item['kvkNummer']}-{$item['vestigingsnummer']}" => "{$item['naam']} - {$item['address']}"];
                        })->toArray();
                    }
                    return [];
                })
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $get) {
                    $cocData = json_decode($get('coc_data'), true);
                    if (is_array($cocData)) {
                        $selectedItem = collect($cocData)->firstWhere(function ($item) use ($state) {
                            return "{$item['kvkNummer']}-{$item['vestigingsnummer']}" === $state;
                        });

                        if ($selectedItem) {
                            // Haal het basisprofiel op
                            /** @var \SaloonKvk\DataTransferObjects\KvkApi\BasisProfielDTO $basicProfile */
                            $basicProfileData = (new KvkBasisProfielRequest($selectedItem['kvkNummer']))->send()->dto();
                            if ($basicProfileData) {
                                $set('name', $basicProfileData->naam ?? '');
                                $set('address', preg_replace('/\s+/', ' ', $basicProfileData->volledigAdres) ?? '');
                                $set('zip_code', $basicProfileData->postcode ?? '');
                                $set('city', $basicProfileData->plaats ?? '');
                                $set('email', $basicProfileData->emailAdres ?? '');
                                $set('phone_number', $basicProfileData->telefoonnummer ?? '');
                            }
                        }
                    }
                })
                ->hidden(fn($get) => !$get('should_show_select'))
                ->dehydrated(false),

            TextInput::make('name')
                ->unique(modifyRuleUsing: function (Unique $rule, $record) {
                    return $rule
                        ->where('tenant_id', auth()->user()->tenant->id)
                        ->ignore($record?->id);
                })
                ->label(__('portal.companies.name'))
                ->required(),

            TextInput::make('email')
                ->label(__('portal.email'))
                ->required(fn() => !empty($view))
                ->email(),

            TextInput::make('phone_number')
                ->required(fn() => !empty($view))
                ->label(__('portal.companies.phone_number')),

            TextInput::make('address')
                ->required(fn() => !empty($view))
                ->label(__('portal.companies.address')),

            TextInput::make('zip_code')
                ->required(fn() => !empty($view))
                ->label(__('portal.companies.zip_code')),

            TextInput::make('city')
                ->required(fn() => !empty($view))
                ->label(__('portal.companies.city')),
        ];
    }

    public static function getWizardSchema(): array
    {
        return [
            Wizard::make([
                Wizard\Step::make(__('portal.clients.client'))->icon('heroicon-o-rectangle-stack')->schema(self::getFormSchema()),
                Wizard\Step::make(__('portal.users.contacts'))->icon('heroicon-o-user-group')->schema([
                    Repeater::make('users')
                        ->schema([
                            TextInput::make('first_name')
                                ->label(__('portal.profile.info.first_name'))
                                ->reactive(),
                            TextInput::make('last_name')
                                ->label(__('portal.profile.info.last_name'))
                                ->reactive(),
                            TextInput::make('email')
                                ->label(__('portal.email'))
                                ->email()
                                ->distinct()
                                ->requiredWith('email, first_name, last_name'),
                            Grid::make()
                                ->schema([
                                    Toggle::make('login_allowed')
                                        ->label(__('portal.users.login_allowed'))
                                        ->hidden(fn($record) => $record?->isAdmin())
                                        ->default(false)
                                        ->reactive()
                                        ->columnSpan(1),
                                    Toggle::make('email_enabled')
                                        ->label(__('portal.users.email_enabled'))
                                        ->default(false)
                                        ->columnSpan(1),
                                    Toggle::make('should_invite')
                                        ->label(__('portal.users.should_invite'))
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set){
                                            if($state === true){
                                                $set('login_allowed', $state);
                                            }
                                        })
                                ])
                                ->columns(3)
                        ])
                        ->cloneable()
                        ->collapsible()
                        ->createItemButtonLabel(__('portal.users.add_user'))
                        ->itemLabel(fn (array $state): ?string => ($state['first_name'] ?? null) . ' ' . ($state['last_name'] ?? null))
                        ->minItems(0)
                ])->model(User::class),
            ])->columnSpanFull()
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
            'view' => Pages\ViewCompany::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getRelations(): array
    {
        return [
            OpportunitiesRelationManager::class,
            CompanyUsersRelationManager::class,
            PinboardItemsRelationManager::class,
            CallEventsRelationManager::class
        ];
    }

    public static function getModelLabel(): string
    {
        return __('portal.clients.client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.clients.clients');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Company::class);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Company::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete', Company::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', $record);
    }

    public static function getDocumentation(): array|string
    {
        return [
            'clients',
        ];
    }
}
