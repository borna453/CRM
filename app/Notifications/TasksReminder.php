<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\Task;
use App\Models\User;
use App\Utils\Notifications\NotificationButtonHelper;
use App\Utils\Notifications\NotificationHelper;
use Filament\Notifications\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TasksReminder extends BaseNotification implements ShouldQueue
{
    public function __construct(
        public Collection $tasks, public User $user
    )
    {
        $this->message = __('portal.notifications.task.reminder');
        parent::__construct(['tasks' => $this->tasks, 'user' => $this->user]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::TASKS_REMINDER->value;
    }

    public function via(object $notifiable): array
    {
        if($notifiable->email_enabled) {
            return ['mail', 'database'];
        }
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $multiple = $this->tasks->count() > 1;

        if ($multiple) {
            $this->message = __('portal.notifications.task.multiple_reminder');
            $bodyMessage = $this->message;
            $url = $this->buildUrl($notifiable, true);
        } else {
            $task = $this->tasks->first();
            $url = $this->buildUrl($notifiable, false, $task->id);
            $bodyMessage = $this->message . " " . $task->title . ".";
        }

        $notification = \Filament\Notifications\Notification::make()
            ->title(__('portal.notifications.task.subject_reminder'))
            ->body($bodyMessage)
            ->actions([
                Action::make('openTaskModal')
                    ->label(__('portal.notifications.task.view'))
                    ->url($url)
            ]);

        if (!$multiple) {
            $notification->viewData(['model_id' => $this->tasks->first()->id, 'model_type' => Task::class]);
        }

        return $notification->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        $emailContent = $this->emailTemplate->email_content;
        $params = $this->templateParams($this->emailTemplate->button_text);

        $emailContent = NotificationHelper::replaceVariables($emailContent, $params);

        $emailContent = NotificationHelper::replaceTasksTable($emailContent, $params['tasks_table']);

        return (new MailMessage)
            ->subject($this->emailTemplate->email_subject)
            ->markdown('emails.dynamic_notification', [
                'email_content' => $emailContent,
            ]);
    }

    private function buildUrl($notifiable, $multiple, $taskId = null)
    {
        $tenantId = $notifiable->tenant_id;

        if ($notifiable->isAdmin()) {
            $baseRoute = 'filament.admin.resources.tasks.index';
            $routeParams = $multiple ? ['tableFilters[user_id][value]' => $notifiable->id] : ['task_id' => $taskId];
        } else {
            $baseRoute = 'filament.user.resources.tasks.index';
            $routeParams = $multiple ? [] : ['task_id' => $taskId];
        }

        return NotificationHelper::generateTenantUrl($tenantId, $baseRoute, $routeParams);
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $taskRows = $this->tasks->map(function (Task $task) {
            return [
                'title' => $task->title ?? '',
                'deadline' => $task->dt_complete_by->format('d-m-Y') ?? '',
            ];
        })->toArray();

        return [
            'tasks_table' => $taskRows ?? [],
            ...$this->getTenantEmailInformation($this->tasks->first()),
            'button' => NotificationButtonHelper::generateButtonHtml($buttonText, $this->buildUrl($this->user, $this->tasks->count() > 1, $this->tasks->first()->id)),
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'button' => __('portal.notifications.task.parameters.button'),
            ...self::getTenantEmailTemplateInformation(),
            'tasks_table' => __('portal.notifications.task.parameters.tasks_table'),
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification){
            $notification = new self(
                collect(Task::factory()->count(3)->make([
                    'title' => 'Test titel',
                    'dt_complete_by' => now()->subDay(),
                    'information' => 'Test informatie',
                ])),
                User::factory()->make([
                    'name' => auth()->user()->name
                ])
            );
        });

        return $notification;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }
}
