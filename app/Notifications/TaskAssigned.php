<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\Task;
use App\Models\User;
use App\Utils\Notifications\NotificationButtonHelper;
use App\Utils\Notifications\NotificationHelper;
use Filament\Notifications\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskAssigned extends BaseNotification implements ShouldQueue
{
    public function __construct(
        private readonly Task $task,
        private $currentUserId = null
    )
    {
        $this->message = __('portal.notifications.task.assigned');
        parent::__construct(['task' => $this->task]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::TASK_ASSIGNED->value;
    }

    public function via(object $notifiable): array
    {
        if ($this->currentUserId && $notifiable->id === $this->currentUserId) {
            return [];
        }
        return NotificationHelper::getNotificationChannels($notifiable);
    }

    public function toDatabase(): array
    {
        return \Filament\Notifications\Notification::make()
            ->title(__('portal.notifications.task.subject_assigned'))
            ->body($this->message . " {$this->task->title}.")
            ->actions([
                Action::make('openModal')
                    ->label(__('portal.notifications.task.view'))
                    ->url(NotificationHelper::generateTenantUrl($this->task->tenant_id, 'filament.user.resources.tasks.index', ['model_id' => $this->task->id])),
            ])
            ->viewData(['model_id' => $this->task->id, 'model_type' => Task::class])
            ->getDatabaseMessage();
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.task.view');

        if (!$preview){
            $baseRoute = $this->task->user->isAdmin() ? 'filament.admin.resources.tasks.index' : 'filament.user.resources.tasks.index';

            $taskUrl = NotificationHelper::generateTenantUrl($this->task->tenant?->id, $baseRoute, ['model_id' => $this->task->id]);
        }

        return [
            'title' => $this->task->title ?? '',
            'creator' => $this->task->createdBy?->name ?? '',
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $taskUrl ?? '#'),
            'deadline' => $this->task->dt_complete_by?->format('d-m-Y') ?? '',
            'information' => $this->task->information ?? '',
            ...$this->getTenantEmailInformation($this->task),
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'title' => __('portal.notifications.task.parameters.task_title'),
            'creator' => __('portal.notifications.task.parameters.task_creator'),
            'button' => __('portal.notifications.task.parameters.button'),
            'deadline' => __('portal.notifications.task.parameters.deadline'),
            'information' => __('portal.notifications.task.parameters.information'),
            ...self::getTenantEmailTemplateInformation(),
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification){
            $notification = new self(
                Task::factory()::preview([
                    'title' => 'Test titel',
                    'dt_complete_by' => now(),
                    'information' => 'Test informatie',
                ])
                    ->setRelation('user', User::factory()->make([
                        'name' => 'Test User'
                    ]))
                    ->setRelation('created_by', User::factory()->make([
                        'name' => 'Test Creator',
                    ]))
            );
        });

        return $notification;
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}
