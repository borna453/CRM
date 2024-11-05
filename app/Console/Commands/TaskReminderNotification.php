<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TasksReminder;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Console\Command;

class TaskReminderNotification extends Command
{
    protected $signature = 'tasks:remind';

    protected $description = 'Sends a reminder to users that need to complete their tasks today.';
    public array $taskUsers = [];

    public function handle()
    {
        $tasks = Task::due()
                    ->open()
                    ->with('tenant')
                    ->with('user.tenant')
                    ->get()
                    ->groupBy('user_id');

        foreach ($tasks as $userTasks) {
            if (! ($user = $userTasks->first()->user)) {
                continue;
            }

            $user->tenant->run(function () use ($user, $userTasks) {
                $user->notify(new TasksReminder($userTasks, $user));
                event(new DatabaseNotificationsSent($user));
            });
        }
    }
}
