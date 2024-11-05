<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Task;
use App\Models\User;
use App\Utils\Filament\Actions\CompleteActionHelper;
use App\Utils\Filament\Actions\TaskEditActionHelper;
use App\Utils\Filament\Actions\UncompleteActionHelper;
use App\Utils\Filament\Tables\TaskIconHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Livewire\Component;

class TasksTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $appointment;

    protected $listeners = ['refreshTaskTable' => '$refresh'];

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table->query(
            Task::query()->whereModelType(Appointment::class)
                ->whereModelId($this->appointment->id)->orderByDeadlineDate()
            )
            ->columns([
                TextInputColumn::make('title')
                    ->label(__('portal.tasks.title'))
                    ->extraAttributes(['class' => 'w-full']),

                SelectColumn::make('user_id')
                    ->label(__('portal.users.contacts'))
                    ->options(fn ($record) => User::assignableUsers()
                        ->where('company_id', $record->user->company_id)
                      ->pluck('name', 'id')),

                TextColumn::make('dt_complete_by')
                    ->label(__('portal.complete_by'))
                    ->date('d-m-Y')
                    ->icon(fn($record) => TaskIconHelper::getIcon($record))
                    ->iconColor(fn($record) => TaskIconHelper::getIconColor($record)),
            ])
            ->recordClasses(function ($record){
                if($record->dt_is_completed){
                    return 'bg-green-100';
                }
            })
            ->filters([
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('portal.tasks.create'))
                    ->form([
                        TextInput::make('title')
                            ->label(__('portal.tasks.title'))
                            ->autofocus()
                            ->required(),
                        Textarea::make('information')
                            ->rows(4)
                            ->label(__('portal.tasks.information')),
                        DatePicker::make('dt_complete_by')->label(__('portal.complete_by'))
                    ])
                    ->modalHeading(__('portal.tasks.create'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['model_type'] = 'App\\Models\\Appointment';
                        $data['model_id'] = $this->appointment->id;
                        if (auth()->user()->isUser()) {
                            $data['user_id'] = auth()->id();
                        } else{
                            $data['user_id'] =  $this->appointment->user_id;
                        }
                        return $data;
                    })
            ])
            ->actions([
                CompleteActionHelper::completeAction(),
                UncompleteActionHelper::uncompleteAction(),
                TaskEditActionHelper::edit(),
                DeleteAction::make()->hiddenLabel()->modalHeading(__('portal.delete_task')),
            ],position: ActionsPosition::BeforeColumns)
            ->selectable(false)
            ->paginated(false)
            ->emptyStateHeading(__('portal.tasks.no_tasks'))
            ->emptyStateDescription(__('portal.tasks.no_tasks_description'));
    }

    public function getTableModelLabel(): ?string
    {
        return __('portal.tasks.task');
    }

    public function render()
    {
        return view('livewire.appointments.tasks-table');
    }
}
