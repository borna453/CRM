<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Utils\Filament\Actions\CompleteActionHelper;
use App\Utils\Filament\Actions\UncompleteActionHelper;
use App\Utils\Filament\Tables\TaskIconHelper;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label(__('portal.tasks.title'))
                    ->required()
                    ->autofocus()
                    ->columnSpanFull(),
                Textarea::make('information')
                    ->columnSpanFull()
                    ->label(__('portal.tasks.information'))
                    ->rows(4)
                    ->columnSpanFull(),
                DatePicker::make('dt_complete_by')
                    ->placeholder(__('portal.date_format'))
                    ->label(__('portal.complete_by'))
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_private')
                    ->visible(function ($record) {
                        if ($record) {
                            return $record->user_id === auth()->id();
                        }
                    })
                    ->label(__('portal.tasks.is_private')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('portal.tasks.title'))
                    ->wrap(),
                TextColumn::make('dt_complete_by')
                    ->date('d-m-Y')
                    ->label(__('portal.complete_by'))
                    ->icon(fn($record) => TaskIconHelper::getIcon($record))
                    ->iconColor(fn($record) => TaskIconHelper::getIconColor($record)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('portal.tasks.create')),
            ])
            ->actions([
                CompleteActionHelper::completeAction(),
                UncompleteActionHelper::uncompleteAction(),
                Tables\Actions\EditAction::make('edit')
                    ->hidden(function ($record) {
                        return !is_null($record->deleted_at) || $record->dt_is_completed;
                    })
                    ->label('')
                    ->modalHeading(__('portal.tasks.edit'))
                    ->extraModalFooterActions([
                        Action::make('complete')
                            ->color('success')
                            ->label(__('portal.complete'))
                            ->hidden(fn ($record) => !is_null($record->deleted_at))
                            ->visible(function ($record) {
                                return !$record->dt_is_completed;
                            })
                            ->action(function ($record, $livewire) {
                                $record->complete();

                                $livewire->dispatch('confetti');
                            }),
                        DeleteAction::make()
                            ->color('danger')
                            ->visible(function ($record){
                                return $record->created_by === auth()->id();
                            })
                            ->successRedirectUrl(url()->previous())
                    ])
                    ->form([
                        TextInput::make('title')
                            ->label(__('portal.tasks.title'))
                            ->required(),
                        Textarea::make('information')
                            ->rows(4)
                            ->label(__('portal.tasks.information')),
                        DatePicker::make('dt_complete_by')->label(__('portal.complete_by')),
                        Forms\Components\Toggle::make('is_private')
                            ->visible(function ($record) {
                                if ($record) {
                                    return $record->user_id === auth()->id();
                                }
                            })
                            ->label(__('portal.tasks.is_private')),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(__('portal.tasks.delete'))
                    ->label('')
                    ->visible(fn ($record) => $record->created_by === auth()->id()),
                Tables\Actions\Action::make('restore')
                    ->label(__('portal.restore'))
                    ->icon('heroicon-s-arrow-path')
                    ->visible(fn($record) => $record->trashed() && $record->created_by === auth()->id())
                    ->action(fn($record) => $record->restore())
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->recordUrl(null)
            ->recordAction(function ($record){
                return !$record->deleted_at && !$record->dt_is_completed ? Tables\Actions\EditAction::class : null;
            })
            ->emptyStateHeading(__('portal.tasks.no_tasks'))
            ->emptyStateDescription(__('portal.tasks.no_tasks_description'))
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Task::whereVisibleTo(auth()->user())->open()->count();

        return $count > 0 ? $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return Task::query()->whereVisibleTo(auth()->user());
    }

    public static function getModelLabel(): string
    {
        return __('portal.tasks.task');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.tasks.tasks');
    }

    public static function getDocumentation(): array|string
    {
        return [
            'tasks',
        ];
    }
}
