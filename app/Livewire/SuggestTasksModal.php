<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;

class SuggestTasksModal extends Component
{
    public array $tasks = [];
    public string $modelType = '';
    public int $modelId;

    public function mount($tasks, $modelType, $modelId): void
    {
        $this->tasks = $tasks;
        $this->modelType = $modelType;
        $this->modelId = $modelId;
    }

    public function createTask(int $index)
    {
        $task = $this->tasks[$index];

        Task::create([
            'title' => $task['title'],
            'model_id' => $this->modelId,
            'model_type' => $this->modelType,
            'user_id' => $task['user_id'],
            'dt_complete_by' => $task['completed_by'],
            'created_by' => auth()->id(),
        ]);

        unset($this->tasks[$index]);

        $this->tasks = array_values($this->tasks);

        if (empty($this->tasks)) {
            $this->dispatch('close-modal', id: 'suggest-tasks-modal');
        }

        $this->dispatch('refreshTaskTable');
    }

    public function removeTask(int $index)
    {
        unset($this->tasks[$index]);

        $this->tasks = array_values($this->tasks);

        if (empty($this->tasks)) {
            $this->dispatch('close-modal', id: 'suggest-tasks-modal');
        }
    }

    public function createAllTasks()
    {
        foreach ($this->tasks as $task) {
            Task::create([
                'title' => $task['title'],
                'model_id' => $this->modelId,
                'model_type' => $this->modelType,
                'user_id' => $task['user_id'],
                'dt_complete_by' => $task['completed_by'],
                'created_by' => auth()->id(),
            ]);
        }

        $this->tasks = [];

        $this->dispatch('close-modal', id: 'suggest-tasks-modal');

        $this->dispatch('refreshTaskTable');
    }

    public function render()
    {
        return view('livewire.suggest-tasks-modal');
    }
}
