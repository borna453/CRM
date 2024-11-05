<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TasksReminder;
use Illuminate\Console\Command;

class DailyTasksReminder extends Command
{
    protected $signature = 'tasks:daily-reminder';
    protected $description = 'Check for tasks with deadlines today and send reminders.';

    public function handle()
    {
        $tasks = Task::dueToday()
            ->with('user.tenant')
            ->open()
            ->get()
            ->groupBy('user_id');

        foreach ($tasks as $userTasks) {
            if (! ($user = $userTasks->first()->user)) {
                continue;
            }
            $user->tenant->run(function () use ($user, $userTasks) {
                $user->notify(new TasksReminder($userTasks));
            });
        }
    }
}
