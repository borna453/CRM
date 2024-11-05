<?php

namespace App\Filament\Resources;

use App\Enums\PrimaryColor;
use App\Enums\ViewOptions;
use App\Filament\Pages\OpportunitiesKanbanBoard;
use App\Filament\Resources\OpportunityResource\Pages;
use App\Livewire\CompactCompanyCard;
use App\Livewire\OpportunityModalForm;
use App\Livewire\OpportunityNotesView;
use App\Livewire\OpportunityTasksView;
use App\Models\Company;
use App\Models\Label;
use App\Models\Note;
use App\Models\Opportunity;
use App\Models\Task;
use App\Utils\Filament\Actions\OpportunityActionHelper;
use App\Utils\Filament\Actions\QuickActionsHelper;
use App\Utils\RichEditorButtons;
use DOMDocument;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Color\Rgb;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static ?string $slug = 'opportunities';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public $companyId;

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();

        return $form
            ->schema(self::getFormSchema(recordId: $record?->id));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters([
                //
            ])
            ->actions([
                OpportunityActionHelper::closeOpportunity(),
                OpportunityActionHelper::openOpportunity(),
                EditAction::make()
                          ->hiddenLabel()
                          ->fillForm(function ($record) {
                              $record->load('notes');

                              $formState = $record->attributesToArray();

                              $formState['notes'] = $record->notes->isNotEmpty() ? ['note' => $record->notes->first()->note] : ['note' => null];

                              return $formState;
                          })
                          ->action(function ($data,
                              $record) {
                              unset($data['notes'], $data['task']);

                              $record->update($data);
                          })
                          ->extraModalFooterActions([
                              DeleteAction::make()
                                          ->successRedirectUrl(url()->previous())
                                          ->modalHeading(function ($record) {
                                              return $record->title.' '.strtolower(__('portal.delete'));
                                          }),
                          ]),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getFormSchema($view = null,
        $recordId = null): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tabs\Tab::make(__('portal.opportunities.opportunity'))
                            ->schema([
                                Select::make('company_id')
                                      ->label(__('portal.companies.company'))
                                      ->columnSpanFull()
                                      ->relationship('company', 'name')
                                      ->searchable()
                                      ->options(fn() => Company::withoutAdminCompany()
                                                               ->pluck('name', 'id'))
                                      ->preload()
                                      ->reactive()
                                      ->live()
                                      ->afterStateUpdated(function ($state,
                                          $set,
                                          $livewire,
                                          $get,
                                          $context) {
                                          if ($context !== OpportunityModalForm::class && !is_null($state)) {
                                              $set('companyId', $state);
                                              $set('showCard', true);
                                              $set('hideSelect', true);
                                              $set('company-id', $state);

                                              $livewire->dispatch('companyIdUpdated', $state);
                                              $livewire->dispatch('showCompanyCard');
                                          }
                                      })
                                      ->hidden(function ($get,
                                          $record,
                                          $set) {
                                          if ($record?->company_id && $get('hideSelect') === null) {
                                              $set('showCard', true);

                                              return true;
                                          }

                                          if ($get('hideSelectAfterCompanyCreate') === true) {
                                              return true;
                                          }

                                          return $get('hideSelect');
                                      })
                                      ->visible(fn() => empty($view))
                                      ->createOptionForm(CompanyResource::getFormSchema('onboarding'))
                                      ->createOptionAction(function ($action) {
                                          return $action->action(function ($state,
                                              $data,
                                              $set) {
                                              $company = Company::create($data);

                                              $set('company_id', $company->id);
                                              $set('hideSelectAfterCompanyCreate', true);
                                              $set('showCard', true);
                                          });
                                      })
                                      ->afterStateUpdated(function ($state,
                                          $set) {
                                          if ($state) {
                                              $set('company_id', $state);
                                              $set('showCard', true);
                                              $set('hideSelect', true);
                                          }
                                      }),
                                Hidden::make('hideSelect')
                                      ->default(false)
                                      ->reactive()
                                      ->live()
                                      ->dehydrated(false),
                                Hidden::make('hideSelectAfterCompanyCreate')
                                      ->default(false)
                                      ->reactive()
                                      ->live()
                                      ->dehydrated(false),
                                Hidden::make('companyId')
                                      ->reactive()
                                      ->live()
                                      ->dehydrated(false),
                                Hidden::make('company_id')
                                      ->id('company-id')
                                      ->reactive()
                                      ->live()
                                      ->dehydrated(function ($get) {
                                          return $get('hideSelect') || $get('hideSelectAfterCompanyCreate');
                                      }),
                                Hidden::make('showCard')
                                      ->reactive()
                                      ->live()
                                      ->dehydrated(false),
                                Livewire::make(CompactCompanyCard::class, fn(Get $get) => ['companyId' => $get('company_id')])
                                        ->reactive()
                                        ->key(fn($record) => 'company-card-'.($record?->id ?? 0))
                                        ->live()
                                        ->dehydrated(false)
                                        ->label(__('portal.companies.company'))
                                        ->visible(function ($get,
                                            $context,
                                            $state,
                                            $set) {
                                            $set('company_id', $get('company_id'));

                                            return $get('showCard');
                                        }),
                                TextInput::make('title')
                                         ->label(__('portal.opportunities.title'))
                                         ->required()
                                         ->columnSpanFull(),
                                RichEditor::make('text')
                                          ->label(__('portal.opportunities.text'))
                                          ->columnSpanFull()
                                          ->toolbarButtons(RichEditorButtons::$toolbarButtons),
                                Grid::make()
                                    ->schema([
                                        Select::make('label_id')
                                              ->label(__('portal.opportunities.label'))
                                              ->columns(1)
                                              ->allowHtml()
                                              ->native(false)
                                              ->selectablePlaceholder(false)
                                              ->createOptionForm(LabelResource::getFormSchema(ViewOptions::OPPORTUNITY))
                                              ->createOptionUsing(fn($data) => Label::create($data)->id)
                                              ->createOptionAction(fn($action) => $action->modalWidth('4xl'))
                                              ->createOptionModalHeading(__('portal.labels.create'))
                                              ->options(
                                                  Label::all()
                                                       ->mapWithKeys(function ($label) {
                                                           $colorEnum = PrimaryColor::tryFrom($label->color);

                                                           if ($colorEnum !== null) {
                                                               $colorRgb = $colorEnum->getColor()[600];
                                                               $colorHex = Rgb::fromString("rgb({$colorRgb})")
                                                                              ->toHex();
                                                           } else {
                                                               $colorHex = '#000000';
                                                           }

                                                           return [
                                                               $label->id => "<span class='flex items-center gap-x-4'>
                                <span class='rounded-full w-4 h-4' style='background-color:{$colorHex};'></span>
                                <span>{$label->name}</span>
                                </span>",
                                                           ];
                                                       })
                                                       ->toArray()
                                              )
                                              ->default(Label::where('order_column', '>', 0)
                                                             ->first()?->id)
                                              ->columnSpan(4),
                                        TextInput::make('expected_revenue')
                                                 ->prefix('€')
                                                 ->label(__('portal.opportunities.expected_revenue'))
                                                 ->columnSpan(4),
                                        TextInput::make('cost_estimate')
                                                 ->prefix('€')
                                                 ->label(__('portal.opportunities.cost_estimate'))
                                                 ->columnSpan(4),
                                    ])
                                    ->columns(12),
                            ]),
                    Tabs\Tab::make(__('portal.notes.notes'))
                            ->schema([
                                RichEditor::make('note')
                                          ->label(__('portal.notes_add'))
                                          ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                                          ->visible(function ($context) {
                                              return $context === 'create' || $context === OpportunityModalForm::class || $context === 'create_opportunity';
                                          })
                                          ->columnSpanFull(),
                                Livewire::make(OpportunityNotesView::class, ['opportunityId' => $recordId])
                                        ->reactive()
                                        ->key(fn() => 'notes-view-'.$recordId.'-'.now()->timestamp)
                                        ->live()
                                        ->dehydrated(false)
                                        ->visible(fn($context) => $context === 'edit' || $context === OpportunitiesKanbanBoard::class)
                                        ->lazy()
                                        ->columnSpanFull(),
                            ])
                            ->model(Note::class)
                            ->statePath('notes'),
                    Tabs\Tab::make('tasks')
                            ->label(__('portal.tasks.tasks'))
                            ->schema([
                                Livewire::make(OpportunityTasksView::class, ['opportunityId' => $recordId])
                                        ->reactive()
                                        ->lazy()
                                        ->key(fn() => 'tasks-view-'.$recordId.'-'.now()->timestamp)
                                        ->live()
                                        ->dehydrated(false)
                                        ->visible(fn($context) => $context === 'edit' || $context === OpportunitiesKanbanBoard::class)
                                        ->columnSpanFull(),
                                Grid::make()
                                    ->schema(
                                        QuickActionsHelper::taskActionModalForm(ViewOptions::OPPORTUNITY)
                                    )
                                    ->visible(fn($context) => $context === 'create' || $context === OpportunityModalForm::class || $context === 'create_opportunity')
                                    ->model(Task::class),
                            ])
                            ->model(Task::class)
                            ->statePath('task'),
                ])
                ->columnSpanFull(),
        ];
    }

    public static function getColumns($view = null): array
    {
        return [
            TextColumn::make('company_id')
                      ->label(__('portal.companies.company'))
                      ->formatStateUsing(fn($record) => $record->company?->name)
                      ->searchable()
                      ->visible(fn() => empty($view))
                      ->sortable(),
            TextColumn::make('title')
                      ->label(__('portal.opportunities.text'))
                      ->formatStateUsing(function ($record) {
                          $text = $record->text;
                          if (!empty($text)) {
                              $dom = new DOMDocument();
                              @$dom->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'));
                              $figures = $dom->getElementsByTagName('figure');
                              while ($figures->length > 0) {
                                  $figure = $figures->item(0);
                                  $figure->parentNode->removeChild($figure);
                              }
                              $filteredHtml = $dom->saveHTML();
                          } else {
                              $filteredHtml = '';
                          }

                          return '
            <div>
                <div class="font-bold mb-1">'.e($record->title).'</div>
                <div>'.$filteredHtml.'</div>
            </div>';
                      })
                      ->wrap()
                      ->searchable()
                      ->sortable()
                      ->html(),
            TextColumn::make('expected_revenue')
                      ->label(__('portal.opportunities.expected_revenue'))
                      ->formatStateUsing(fn($record) => $record->formatted_revenue)
                      ->searchable()
                      ->sortable(),
            TextColumn::make('cost_estimate')
                      ->label(__('portal.opportunities.cost_estimate'))
                      ->formatStateUsing(fn($record) => $record->formatted_cost_estimate)
                      ->searchable()
                      ->sortable(),
            TextColumn::make('label_id')
                      ->badge()
                      ->formatStateUsing(fn($record) => $record->label->name)
                      ->label(__('portal.opportunities.label'))
                      ->color(fn($record) => $record->label->color),
            TextColumn::make('created_at')
                      ->label(__('portal.created_at'))
                      ->date('d-m-Y'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOpportunities::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return Opportunity::with(['company',
                                  'label']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Opportunity::open()
                            ->count();

        return $count > 0 ? $count : null;
    }

    public static function getModelLabel(): string
    {
        return __('portal.opportunities.opportunity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.opportunities.opportunities');
    }

    public static function canViewAny(): bool
    {
        return auth()
            ->user()
            ->can('viewAny', Opportunity::class);
    }

    public static function canCreate(): bool
    {
        return auth()
            ->user()
            ->can('create', Opportunity::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()
            ->user()
            ->can('update', Opportunity::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()
            ->user()
            ->can('delete', Opportunity::class);
    }

    public static function getDocumentation(): array|string
    {
        return [
            'opportunities',
        ];
    }
}
