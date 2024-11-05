 <?php

use App\Filament\User\Resources\TaskResource\Pages\ListTasks;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

it('sends a task assigned notification when a task is created', function () {
    Notification::fake();


    $task = Task::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Complete this task',
    ]);

    Notification::assertSentTo(
        $this->regularUser,
        TaskAssigned::class,
        function ($notification) use ($task) {
            return $notification->getTask()->id === $task->id;
        }
    );
});

it('contains correct content in mail notification', function () {
    Notification::fake();

    $user = $this->regularUser;
    Task::create([
        'user_id' => $user->id,
        'title' => 'Urgent Task',
    ]);

    Notification::assertSentTo($user, TaskAssigned::class, function ($notification) use ($user) {
        return Str::contains($notification->getEmailContent(), 'Urgent Task');
    });
});

it('does not send notification if task has no assigned user', function () {
    Notification::fake();

    Task::create([
        'title' => 'Task with no assigned user',
    ]);

    Notification::assertNothingSent();
});

it('sends a task assigned notification to the user', function () {
    Notification::fake();

    $user = $this->regularUser;

    $task = Task::create([
        'title' => 'Complete this task',
        'user_id' => $user->id,
    ]);

    $user->notify(new TaskAssigned($task, $this->adminUser->id));

    Notification::assertSentTo(
        [$user], TaskAssigned::class, function ($notification, $channels) use ($task) {
        $databaseNotification = $notification->toDatabase();

        expect($databaseNotification['title'])->toBe(__('portal.notifications.task.subject_assigned'))
            ->and($databaseNotification['body'])->toBe(__('portal.notifications.task.assigned') . " $task->title.")
            ->and($databaseNotification['actions'][0]['label'])->toBe(__('portal.notifications.task.view'))
            ->and($databaseNotification['actions'][0]['url'])->toBe($this->tenant->route('filament.user.resources.tasks.index', ['model_id' => $task->id]));

        return true;
    }
    );
});

it('opens the modal for the specified task from the notification', function () {
    $user = $this->regularUser;

    $task = Task::create([
        'title' => 'Complete this task',
        'user_id' => $user->id,
    ]);
    $this->actingAs($user);

    Livewire::withQueryParams(['model_id' => $task->id])
        ->test(ListTasks::class)
        ->assertSet('model_id', $task->id)
        ->assertDispatched('openModal');
});
