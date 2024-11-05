<?php

namespace App\Livewire;

use App\Models\Opportunity;
use App\Models\Task;
use App\Utils\Filament\Actions\OpportunityChildActionHelper;
use App\Utils\Filament\Actions\QuickActionsHelper;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class OpportunityTasksView extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    public $opportunityId;
    public $opportunity;
    public $tasks = [];

    public function mount($opportunityId)
    {
        $this->opportunityId = $opportunityId;
        $this->opportunity = Opportunity::find($opportunityId);
    }

    #[Computed]
    public function tasks()
    {
        return $this->opportunity?->tasks()->with('user')->orderBy('dt_complete_by', 'desc')->get();
    }

    public function toggleTaskCompletion($taskId)
    {
        $task = $this->opportunity->tasks()->find($taskId);
        if (!$task->dt_is_completed) {
            $task->complete();
            $this->dispatch('confetti');
        } else {
            $task->uncomplete();
        }

        $task->save();
        $this->tasks = $this->tasks();
        $this->dispatch('refreshTasks');
    }

    #[On('refreshTasks')]
    public function refreshTasks()
    {
        $this->opportunity->load('tasks');
    }

    public function createTaskAction(): Action
    {
        return Action::make('createTask')
            ->form(
                QuickActionsHelper::taskActionModalForm()
            )
            ->hiddenLabel()
            ->icon('heroicon-o-plus')
            ->action(function ($data){
                Task::create([
                    'title' => $data['title'],
                    'information' => $data['information'],
                    'dt_complete_by' => $data['dt_complete_by'],
                    'user_id' => $data['user_id'],
                    'model_id' => $this->opportunityId,
                    'model_type' => Opportunity::class
                ]);

                $this->dispatch('openKanbanEdit', [
                    'recordId' => $this->opportunityId,
                    'data' => $data
                ]);

                $this->dispatch('refreshTasks');
            })->modalHeading(OpportunityChildActionHelper::getModalHeading($this));
    }

    public function render()
    {
        return view('livewire.opportunity-tasks-view', ['tasks' => $this->tasks()]);
    }
}
