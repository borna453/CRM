<?php

namespace App\Utils\Livewire;

use App\Models\Task;

class TaskActionHelper
{
    public static function toggleTaskCompletion($taskId, $livewire)
    {
        $task = Task::find($taskId);
        if (!$task->dt_is_completed) {
            $task->complete();
            $livewire->dispatch('confetti');
        } else {
            $task->uncomplete();
        }

        $task->save();
        $livewire->dispatch('refreshTasks');
    }
}
