<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\Permissions;
use App\Models\Task;
use App\Utils\Filament\Actions\CompleteActionHelper;
use App\Utils\Filament\Actions\TaskEditActionHelper;
use App\Utils\Filament\Actions\UncompleteActionHelper;
use App\Utils\Filament\Tables\TaskIconHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TasksRelationManager extends RelationManager
{
    use AdvancedTables;

    protected static string $relationship = 'tasks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label(__('portal.tasks.title'))
                    ->required()
                    ->autofocus()
                    ->columnSpanFull(),
                DatePicker::make('dt_complete_by')
                    ->label(__('portal.complete_by'))
                    ->columnSpanFull(),
                Textarea::make('information')
                    ->label(__('portal.tasks.information'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Task::whereVisibleTo(auth()->user()))
            ->columns([
                TextInputColumn::make('title')
                    ->label(__('portal.tasks.title'))
                    ->extraInputAttributes(['style' => 'width: 200px;']),
                TextColumn::make('dt_complete_by')
                    ->date('d-m-Y')
                    ->label(__('portal.complete_by'))
                    ->icon(fn($record) => TaskIconHelper::getIcon($record))
                    ->iconColor(fn($record) => TaskIconHelper::getIconColor($record)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->label(__('portal.tasks.create')),
            ])
            ->actions([
                Action::make('complete')
                    ->hidden(fn($record) => !is_null($record->deleted_at))
                    ->visible(fn($record) => !$record->dt_is_completed)
                    ->action(function ($record, $livewire) {
                        $record->complete();

                        $livewire->dispatch('confetti');
                    })
                    ->icon('heroicon-o-check')
                    ->iconButton()
                    ->extraAttributes([
                        'class' => 'complete-button',
                    ]),
                UncompleteActionHelper::uncompleteAction(),
                TaskEditActionHelper::edit(),
                DeleteAction::make()->label(''),
            ],position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->selectable(false)
            ->paginated(false)
            ->emptyStateHeading(__('portal.tasks.no_tasks'))
            ->emptyStateDescription(__('portal.tasks.no_tasks_description'));
    }

    public function getPresetViews(): array
    {
        return [
            'open' => PresetView::make()->label(__('portal.open'))
                ->favorite()
                ->icon('heroicon-o-document')
                ->badge(Task::whereUserId($this->ownerRecord->id)->open()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereUserId($this->ownerRecord->id)->open())
                ->default(),
            'closed' => PresetView::make()->label(__('portal.closed'))
                ->favorite()
                ->icon('heroicon-o-check')
                ->badge(Task::whereVisibleTo(auth()->user())->whereUserId($this->ownerRecord->id)->completed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereUserId($this->ownerRecord->id)->completed()->orderBy('dt_is_completed', 'desc')),
            'deleted' => PresetView::make()->label(__('portal.deleted'))
                ->favorite()
                ->icon('heroicon-o-trash')
                ->badge(Task::whereVisibleTo(auth()->user())->onlyTrashed()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereUserId($this->ownerRecord->id)->onlyTrashed()->orderBy('deleted_at', 'desc')),
        ];    }

    public function defaultPresetViewShouldBeApplied(): bool
    {
        return true;
    }

    public static function getModelLabel(): ?string
    {
        return __('portal.tasks.task');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.tasks.tasks');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.tasks.tasks');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->login_allowed && auth()->user()->can('viewAny', Task::class);
    }
}
