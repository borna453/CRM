<?php

namespace App\Observers;

use App\Models\Task;
use App\Notifications\TaskAssigned;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Notifications\DatabaseNotification;

class TaskObserver
{
    public function creating(Task $task): void
    {
        if (auth()->check()) {
            $task->created_by = auth()->id();

            if (!$task->user_id && $task->model) {
                $task->user_id = $task->model->user_id;
            }

            if (auth()->user()->isUser()) {
                $task->user_id = auth()->id();
            }
        }
    }

    public function created(Task $task): void
    {
        if ($task->user) {
            $task->user?->notify(new TaskAssigned($task, auth()->id()));

            event(new DatabaseNotificationsSent($task->user));
        }
    }

    public function updated(Task $task): void
    {
        if ($task->isDirty('user_id') && $task->user_id) {
            $task->load('user');

            $task->user->notify(new TaskAssigned($task, auth()->id()));

            event(new DatabaseNotificationsSent($task->user));
        }
    }

    public function deleted(Task $task): void
    {
        DatabaseNotification::where('data->viewData->model_type', Task::class)
            ->where('data->viewData->model_id', $task->id)
            ->delete();
    }
}
