<?php

namespace App\Filament\Resources;

use App\Enums\OnboardingTypes;
use App\Enums\ViewOptions;
use App\Filament\Resources\CompanyResource\RelationManagers\CompanyUsersRelationManager;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\AppointmentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\PinboardItemsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReportsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\TasksRelationManager;
use App\Jobs\UserWelcomeJob;
use App\Models\Company;
use App\Models\Onboarding;
use App\Models\User;
use App\Notifications\UserWelcomeEmail;
use App\Utils\Filament\Actions\InviteUserActionHelper;
use App\Utils\Filament\Actions\OnboardingActionAttributeHelper;
use App\Utils\Filament\Actions\RestoreActionHelper;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Phpsa\FilamentPasswordReveal\Password;
use RalphJSmit\Filament\Onboard\Http\Livewire\Wizard;
use Spatie\Permission\Models\Role;
use STS\FilamentImpersonate\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 10;

    public static $userRole;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table, bool $relationManager = false, bool $tenantManagement = false): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters([
                //
            ])
            ->actions([
                InviteUserActionHelper::inviteAction()->disabled(fn() => !auth()->user()->can('update', User::class)),
                Impersonate::make()
                    ->redirectTo(function($record){
                        if($record->isAdmin()){
                            return '/admin';
                        }
                        if($record->isEmployee()){
                            return '/admin';
                        }
                        return '/user';
                    })
                    ->visible(fn($record) => ! $tenantManagement && $record->login_allowed && !$record->deleted_at && auth()->user()->can('impersonate', User::class)),
                Tables\Actions\EditAction::make()->visible(fn($record) => !$record->deleted_at)
                    ->successRedirectUrl(fn (Model $record): string => route('filament.admin.resources.companies.edit', [
                    'record' => $record->company,
                ])),
                RestoreActionHelper::restoreAction()->hidden(fn() => !auth()->user()->can('restore', User::class)),
            ])
            ->bulkActions(self::getBulkActions())
            ->recordUrl(function (User $record) use ($relationManager) {
                if ($relationManager || $record->trashed()){
                    return null;
                }

                if(auth()->user()->can('update', User::class)){
                    return UserResource::getUrl('edit', ['record' => $record]);
                }
            })
            ->modifyQueryUsing(function ($query) use ($tenantManagement){
                $query->with('company');

                if(!$tenantManagement){
                    return $query->mainCompanyUsers();
                }
            });
    }

    public static function getFormSchema(string $view = null): array
    {
        $schema = [
            TextInput::make('first_name')
                ->label(__('portal.profile.info.first_name'))
                ->reactive()
                ->live(debounce: 500)
                ->formatStateUsing(function ($state){
                    return ucwords($state);
                })
                ->afterStateUpdated(function ($state, Set $set){
                    $set('first_name', ucwords($state));
                })
                ->required(),
            TextInput::make('last_name')
                ->label(__('portal.profile.info.last_name'))
                ->reactive()
                ->live(debounce: 500)
                ->formatStateUsing(function ($state){
                    return ucwords($state);
                })
                ->afterStateUpdated(function ($state, Set $set){
                    $set('last_name', ucwords($state));
                })
                ->required(),
            TextInput::make('email')
                ->label(__('portal.email'))
                ->email()
                ->required()
                ->unique(
                    'users',
                    'email',
                    ignorable: function ($context, $record) {
                        if($context === Wizard::class){
                            return auth()->user();
                        }
                        return $record;
                    },
                    modifyRuleUsing: fn ($rule) => $rule->where('tenant_id', tenant()->id)
                ),
        ];

        if (empty($view) || $view === ViewOptions::USER_APPOINTMENTS) {
            $schema[] = Select::make('company_id')
                ->searchable()
                ->label(__('portal.companies.company'))
                ->relationship('company', 'name')
                ->reactive()
                ->disabled(fn ($get) => $get('disableCompanySelect'))
                ->default(fn() => Company::where('tenant_id', auth()->user()->tenant_id)->first()->id)
                ->preload()
                ->searchable();
        }
        if (empty($view)){
            $schema[] = Select::make('roles')
                ->searchable()
                ->visible(fn($context) => $context !== 'edit')
                ->default(function ($set) {
                    if(\Request::get("onboard_add_employee") === '1'){
                        $set('company_id', Company::main()->first()->id);
                        $set('hasAdminPanelRole', true);
                        $set('disableCompanySelect', true);
                        return Role::where('name', User::EMPLOYEE)->first()->id;
                    }
                    $userRole = Role::where('name', User::USER)->first();
                    return $userRole ? $userRole->id : null;
                })
                ->label(__('portal.role'))
                ->relationship('roles', 'name', function($query){
                    if(auth()->user()->isAdmin()){
                        $query->where('name', '!=', USER::SUPERADMIN)->where('name', '!=', USER::OWNER);
                    }
                })->options(function () {
                    return Role::where('name', '!=', User::SUPERADMIN)
                        ->where('name', '!=', User::OWNER)
                        ->get()
                        ->mapWithKeys(function ($role) {
                            return [$role->id => __("portal.roles.{$role->name}")];
                        });
                })
                ->reactive()
                ->afterStateUpdated(function ($state, $set){
                    if(Role::where('id', $state)->first()->name === User::ADMIN || Role::where('id', $state)->first()->name === User::EMPLOYEE){
                        $set('company_id', Company::main()->first()->id);
                        $set('disableCompanySelect', true);
                        $set('hasAdminPanelRole', true);
                    }
                    else{
                        $set('disableCompanySelect', false);
                    }
                })
                ->preload()
                ->required()
                ->statePath('userRole')
                ->dehydrated(false);
        }

        if($view !== ViewOptions::ONBOARDING)
        {
            $schema[] = Grid::make()
                ->schema([
                    Toggle::make('login_allowed')
                        ->label(__('portal.users.login_allowed'))
                        ->hidden(fn($record) => $record?->isAdmin())
                        ->reactive(),

                    Toggle::make('email_enabled')
                        ->label(__('portal.users.email_enabled')),

                    Toggle::make('should_invite')
                        ->label(__('portal.users.should_invite'))
                        ->visible(function ($context){
                            if ($context !== 'edit'){
                                return true;
                            }
                        })
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set){
                            if($state === true){
                                $set('login_allowed', $state);
                            }
                        }),
                ])
                ->columns(3); // Adjust the number of columns as needed
        }

        return $schema;
    }

    public static function getColumns(string $view = null): array
    {
        $columns =  [
            Tables\Columns\TextColumn::make('name')
                ->label(__('portal.name'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('email')
                ->label(__('portal.email'))
                ->searchable()
                ->sortable(),
        ];

        if (empty($view)) {
            $columns[] = Tables\Columns\TextColumn::make('roles.name')
                ->label(__('portal.role'))
                ->searchable()
                ->badge()
                ->color(function ($record) {
                    return match (true) {
                        $record->isAdmin() => 'danger',
                        $record->isUser() => 'success',
                        $record->isSuperAdmin() => 'danger',
                        default => 'primary'
                    };
                })
                ->formatStateUsing(fn ($state) => __("portal.roles.{$state}"))
                ->sortable();
        }

        $columns[] = Tables\Columns\IconColumn::make('login_allowed')
                ->disabledClick(function ($record){
                    return $record->isAdmin();
                })
                ->label(__('portal.users.login_allowed'))
                ->boolean()
                ->trueIcon('heroicon-s-check')
                ->falseIcon('heroicon-s-x-mark')
                ->alignCenter()
                ->action(
                    Tables\Actions\Action::make('toggle-login-allowed')
                        ->label(__('portal.users.login_allowed'))
                        ->requiresConfirmation()
                        ->disabled(fn() => !auth()->user()->can('update', User::class))
                        ->modalHeading(function ($record){
                            return $record->login_allowed ? __('portal.users.disable_login') : __('portal.users.enable_login');
                        })
                        ->modalDescription(function ($record){
                            return $record->login_allowed ? __('portal.users.disable_login_confirmation') : __('portal.users.enable_login_confirmation');
                        })
                        ->action(function ($record){
                            $record->update(['login_allowed' => !$record->login_allowed]);
                        })
                );

        $columns[] = Tables\Columns\IconColumn::make('email_enabled')
            ->label(__('portal.users.email_enabled'))
            ->boolean()
            ->trueIcon('heroicon-s-check')
            ->falseIcon('heroicon-s-x-mark')
            ->alignCenter()
            ->action(
                Tables\Actions\Action::make('toggle-email-enabled')
                    ->label(__('portal.users.email_enabled'))
                    ->requiresConfirmation()
                    ->disabled(fn() => !auth()->user()->can('update', User::class))
                    ->modalHeading(function ($record){
                        return $record->email_enabled ? __('portal.users.disable_email') : __('portal.users.enable_email');
                    })
                    ->modalDescription(function ($record){
                        return $record->email_enabled ? __('portal.users.disable_email_confirmation') : __('portal.users.enable_email_confirmation');
                    })
                    ->action(function ($record){
                        $record->update(['email_enabled' => !$record->email_enabled]);
                    })
            );

        return $columns;
    }

    public static function getWizardSchema(): array
    {
        return [
            \Filament\Forms\Components\Wizard::make(function ($get) {
                $steps = [
                    Step::make(__('portal.users.user'))
                        ->icon('heroicon-o-user')
                        ->statePath('user')
                        ->schema(self::getFormSchema(ViewOptions::USER_APPOINTMENTS)),
                ];

                if (!$get('user.company_id')) {
                    $steps[] = Step::make(__('portal.companies.company'))
                        ->icon('heroicon-o-rectangle-stack')
                        ->schema(CompanyResource::getFormSchema())
                        ->model(Company::class);
                }

                return $steps;
            })
                ->reactive()
                ->nextAction(function ($action, $get)
                {
                    if ($get('user.company_id')) {
                        return $action->visible(false);
                    }

                    return $action;
                }),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('invite')->icon('heroicon-o-paper-airplane')->color('primary')->action(function ($records){
                    $records->each(function ($record){
                        if(!$record->invited_at && $record->login_allowed){
                            UserWelcomeJob::dispatch($record);
                        }
                    });
                }),
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            AppointmentsRelationManager::class,
            ReportsRelationManager::class,
            TasksRelationManager::class,
            PinboardItemsRelationManager::class
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(request()->routeIs('filament.admin.resources.users.index'), function ($query){
                return $query->select('users.*')
                    ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->orderByRaw("
            CASE
                WHEN roles.name = 'admin' THEN 1
                WHEN roles.name = 'employee' THEN 2
                WHEN roles.name = 'user' THEN 3
                ELSE 4
            END
        ")
                    ->with('roles');
            })
            ->visibleTo();
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.users.resource_plural');
    }

    public static function getBreadcrumb(): string
    {
        return __('portal.users.resource_plural');
    }

    public static function getModelLabel(): string
    {
        return __('portal.users.resource_singular');
    }

    public static function getModelLabelPlural(): string
    {
        return __('portal.users.resource_plural');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('portal.name') => $record->name,
            __('portal.email') => $record->email,
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', User::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', User::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', User::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete', User::class);
    }

    public static function getDocumentation(): array|string
    {
        return [
            'users',
        ];
    }
}
