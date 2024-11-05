<?php

namespace App\Filament\Resources\ReportResource\RelationManagers;

use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use App\Utils\Filament\Actions\CompleteActionHelper;
use App\Utils\Filament\Actions\TaskEditActionHelper;
use App\Utils\Filament\Actions\UncompleteActionHelper;
use App\Utils\Filament\Tables\TaskIconHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class TaskRelationManager extends RelationManager
{
    use AdvancedTables;

    protected static string $relationship = 'tasks';

    protected $listeners = ['refreshTaskTable' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label(__('portal.tasks.title'))
                    ->columnSpanFull()
                    ->autofocus()
                    ->required(),
                Textarea::make('information')
                    ->label(__('portal.tasks.information'))
                    ->rows(4)
                    ->columnSpanFull(),
                DatePicker::make('dt_complete_by')
                    ->label(__('portal.complete_by'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
      return $table
          ->query(
              Task::whereVisibleTo(auth()->user())->whereModelType(Report::class)->whereModelId($this->ownerRecord->id)->orderByDeadlineDate()
          )
          ->columns([
              TextInputColumn::make('title')
                  ->label(__('portal.tasks.title')),

              SelectColumn::make('user_id')
                  ->label(__('portal.users.contacts'))
                  ->options(User::all()->pluck('name', 'id')),

              TextColumn::make('dt_complete_by')
                  ->label(__('portal.complete_by'))
                  ->date('d-m-Y')
                  ->icon(fn($record) => TaskIconHelper::getIcon($record))
                  ->iconColor(fn($record) => TaskIconHelper::getIconColor($record)),
          ])
          ->headerActions([
              CreateAction::make()
                  ->createAnother(false)
                  ->label(__('portal.tasks.create')),
          ])
          ->actions([
              CompleteActionHelper::completeAction(),
              UncompleteActionHelper::uncompleteAction(),
              TaskEditActionHelper::edit(),
              DeleteAction::make()->label(''),
          ],position: ActionsPosition::BeforeColumns)
          ->bulkActions([
              BulkActionGroup::make([
                  DeleteBulkAction::make(),
              ]),
          ])
          ->selectable(false)
          ->recordClasses(function ($record){
              if($record->dt_is_completed){
                  return 'bg-green-200';
              }
          })
          ->paginated(false)
          ->emptyStateHeading(__('portal.tasks.no_tasks'))
          ->emptyStateDescription(__('portal.tasks.no_tasks_description'));
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
        return $ownerRecord->user->login_allowed && auth()->user()->can('viewAny', Task::class);
    }
}
