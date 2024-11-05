<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\CustomFilamentComment;
use App\Models\Report;
use App\Models\User;
use App\Utils\Notifications\NotificationButtonHelper;
use App\Utils\Notifications\NotificationHelper;
use Filament\Notifications\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Parallax\FilamentComments\Models\FilamentComment;

class CommentCreated extends BaseNotification implements ShouldQueue
{
    private Report $report;

    public function __construct(
        private FilamentComment $comment, private User $notifiable
    )
    {
        $this->message = __('portal.notifications.comment.created');

        $this->report = $this->comment->subject;

        parent::__construct(['comment' => $this->comment, 'report' => $this->report]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::COMMENT_CREATED->value;
    }

    public function via(object $notifiable): array
    {
        return NotificationHelper::getNotificationChannels($notifiable);
    }

    public function toDatabase(): array
    {
        $baseRoute = $this->notifiable->isAdmin() ? 'filament.admin.resources.reports.edit' : 'filament.user.resources.reports.view';

        $url = NotificationHelper::generateTenantUrl($this->report->tenant_id, $baseRoute, ['record' => $this->report->id]);

        return \Filament\Notifications\Notification::make()
            ->title(__('portal.notifications.comment.subject'))
            ->body($this->message . " {$this->report->title}.")
            ->actions([
                Action::make('openReport')
                    ->label(__('portal.notifications.comment.view'))
                    ->url($url)
            ])
            ->viewData(['model_id' => $this->comment->id, 'model_type' => FilamentComment::class])
            ->getDatabaseMessage();
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.comment.view');

        if(!$preview){
            $baseRoute = $this->notifiable->isAdmin() ? 'filament.admin.resources.reports.edit' : 'filament.user.resources.reports.view';

            $commentUrl = NotificationHelper::generateTenantUrl($this->report->tenant->id, $baseRoute, ['record' => $this->report->id]);
        }

        return [
            'report_titel' => $this->report->title ?? '',
            'comment_text' => $this->comment->comment ?? '',
            'comment_creator' => $this->comment->user->name ?? '',
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $commentUrl ?? ''),
            ...$this->getTenantEmailInformation($this->report)
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'report_titel' => __('portal.notifications.comment.report_title'),
            'comment_text' => __('portal.notifications.comment.text'),
            'comment_creator' =>__('portal.notifications.comment.creator'),
            'button' => __('portal.notifications.comment.button'),
            ...self::getTenantEmailTemplateInformation()
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $notification = new self(
                CustomFilamentComment::factory()::preview([
                    'comment' => 'Dit is een test comment',
                ])
                    ->setRelation('subject', Report::factory()::preview([
                        'title' => 'Dit is een test report',
                    ]))
                    ->setRelation('user', User::factory()::preview([
                        'first_name' => 'Jan',
                        'last_name' => 'Jansen',
                    ])),
                User::factory()::preview([
                    'first_name' => 'Jan',
                    'last_name' => 'Jansen',
                ])
            );
        });

        return $notification;
    }
}
