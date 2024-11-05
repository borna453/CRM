<?php

namespace App\Filament\Widgets;

use App\Enums\Permissions;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Utils\Filament\Actions\CompleteActionHelper;
use App\Utils\Filament\Actions\TaskEditActionHelper;
use App\Utils\Filament\Actions\UncompleteActionHelper;
use App\Utils\Filament\Tables\TaskIconHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class OpenTaskWidget extends BaseWidget
{
    use AdvancedTables;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['refreshTasks' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereAssignedTo(auth()->user())->open())
            ->columns([
                TextColumn::make('title')
                    ->label(__('portal.tasks.title'))
                    ->wrap()
                    ->icon(function ($record){
                        if ($record->is_private) {
                            return 'heroicon-s-lock-closed';
                        }
                        return null;
                    })
                    ->iconPosition('after'),
                SelectColumn::make('user_id')
                    ->label(__('portal.users.contact'))
                    ->width('25%')
                    ->options(User::assignableUsers()->pluck('name', 'id'))
                    ->visible(fn () => !auth()->user()->isUser()),
                TextColumn::make('dt_complete_by')
                    ->label(__('portal.complete_by'))
                    ->formatStateUsing(function ($record) {
                        return Carbon::parse($record->dt_complete_by)->format('d-m-Y');
                    })
                    ->icon(fn($record) => TaskIconHelper::getIcon($record))
                    ->iconColor(fn($record) => TaskIconHelper::getIconColor($record)),
            ])
            ->headerActions([
                Tables\Actions\Action::make('tasks_list')
                    ->label(__('portal.view_all'))
                    ->outlined()
                    ->color('secondary')
                    ->url(function (){
                        if(auth()->user()->isUser()) {
                            return \App\Filament\User\Resources\TaskResource::getUrl();
                        }
                        return TaskResource::getUrl();
                    })
            ])
            ->actions([
                CompleteActionHelper::completeAction(),
                UncompleteActionHelper::uncompleteAction(),
                TaskEditActionHelper::edit()->icon('')
            ],position: ActionsPosition::BeforeColumns)
            ->paginated(false)
            ->emptyStateHeading(__('portal.tasks.no_tasks'))
            ->emptyStateDescription(__('portal.tasks.no_tasks_description'))
            ->recordAction(Tables\Actions\EditAction::class);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.tasks.open_tasks');
    }

    public function getTableModelLabel(): ?string
    {
        return __('portal.tasks.create');
    }

    public function getColumnSpan(): int|string|array
    {
        if(auth()->user()->isUser()) {
            return [
                '2xl' => 1,
                'xl' => 2,
            ];
        }

        return 2;
    }

    public static function canView(): bool
    {
        if(Filament::getCurrentPanel()->getId() === User::ADMIN){
            return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_OPEN_TASK_WIDGET->value);
        }

        if(Filament::getCurrentPanel()->getId() === User::USER){
            return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_USER_OPEN_TASK_WIDGET->value);
        }
    }
}
