<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\User;
use App\Utils\Filament\Actions\CompleteActionHelper;
use App\Utils\Filament\Actions\TaskEditActionHelper;
use App\Utils\Filament\Actions\UncompleteActionHelper;
use App\Utils\Filament\FormFields\GazeHelper;
use App\Utils\Filament\Tables\TaskIconHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?int $navigationSort = 6;

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
                    ->label(__('portal.tasks.information'))
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->options(User::assignableUsers()->pluck('name', 'id'))
                    ->label(__('portal.users.contacts'))
                    ->searchable()
                    ->columnSpanFull(),
                DatePicker::make('dt_complete_by')
                    ->label(__('portal.complete_by'))
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('portal.tasks.title'))
                    ->wrap(),
                TextColumn::make('user_id')
                    ->label(__('portal.users.contacts'))
                    ->searchable()
                    ->formatStateUsing(function ($record){
                        return $record->user->name;
                    }),
                TextColumn::make('dt_complete_by')
                    ->date('d-m-Y')
                    ->label(__('portal.complete_by'))
                    ->icon(fn($record) => TaskIconHelper::getIcon($record))
                    ->iconColor(fn($record) => TaskIconHelper::getIconColor($record)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('portal.users.contacts'))
                    ->options(User::assignableUsers()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                CompleteActionHelper::completeAction(),
                UncompleteActionHelper::uncompleteAction(),
                TaskEditActionHelper::edit(),
                Tables\Actions\Action::make('restore')
                    ->label(__('portal.restore'))
                    ->icon('heroicon-s-arrow-path')
                    ->visible(fn($record) => $record->trashed() && $record->created_by === auth()->id())
                    ->hidden(fn() => !auth()->user()->can('restore', Task::class))
                    ->action(fn($record) => $record->restore())
            ],position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->selectable(false)
            ->recordAction(function ($record){
                return !$record->deleted_at && !$record->dt_is_completed ? Tables\Actions\EditAction::class : null;
            })
            ->paginated(false);

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return Task::with('user');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Task::whereVisibleTo(auth()->user())->open()->count();

        return $count > 0 ? $count : null;
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.tasks.tasks');
    }

    public static function getBreadcrumb(): string
    {
        return __('portal.tasks.tasks');
    }

    public static function getModelLabel(): string
    {
        return __('portal.tasks.task');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.tasks.tasks');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', Task::class) && $record->created_by === auth()->id();
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Task::class);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Task::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete', Task::class);
    }
}
