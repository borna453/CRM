<?php

use App\Models\Task;
use App\Notifications\TasksReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

it('sends a task reminder notification for tasks due today', function () {
    Notification::fake();

    $task = Task::create([
        'dt_complete_by' => Carbon::today(),
        'user_id' => $this->regularUser->id,
        'title' => 'Task due today',
    ]);

    $this->artisan('tasks:remind');

    Notification::assertSentTo(
        $this->regularUser,
        TasksReminder::class,
        function (TasksReminder $notification) use ($task) {
            return $notification->tasks->first()->id === $task->id;
        }
    );
});

it('correctly sets notification channels and content', function () {
    Notification::fake();

    Task::create([
        'dt_complete_by' => Carbon::today(),
        'user_id' => $this->regularUser->id,
        'title' => 'Task due today',
    ]);

    $this->artisan('tasks:remind');

    Notification::assertSentTo($this->regularUser, TasksReminder::class, function (TasksReminder $notification, $channels) {
        return in_array('database', $channels) && in_array('mail', $channels);
    });
});

