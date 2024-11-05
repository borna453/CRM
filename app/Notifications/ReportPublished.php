<?php

namespace App\Notifications;


use App\Enums\NotificationTypeEnum;
use App\Models\Report;
use App\Utils\Notifications\NotificationButtonHelper;
use App\Utils\Notifications\NotificationHelper;
use Filament\Notifications\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportPublished extends BaseNotification implements ShouldQueue
{
    public function __construct(
        private readonly Report $report
    )
    {
        $this->message = __('portal.notifications.report.published');
        parent::__construct(['report' => $this->report]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::REPORT_PUBLISHED->value;
    }

    public function via(object $notifiable): array
    {
        return NotificationHelper::getNotificationChannels($notifiable);
    }

    public function toMail($notifiable): MailMessage
    {
        $reportUrl = NotificationHelper::generateTenantUrl($this->report?->tenant?->id, 'filament.user.resources.reports.view', ['record' => $this->report->id]);

        $params = $this->templateParams($this->emailTemplate->button_text, $reportUrl);

        $emailContent = NotificationHelper::replaceVariables($this->emailContent, $params);

        return (new MailMessage)
            ->subject($this->emailTemplate->email_subject)
            ->markdown('emails.dynamic_notification', [
                'email_content' => $emailContent,
            ]);
    }

    public function toDatabase(): array
    {
        return \Filament\Notifications\Notification::make()
            ->title(__('portal.notifications.report.subject'))
            ->body($this->message . " {$this->report->title}.")
            ->actions([
                Action::make('openReport')
                    ->label(__('portal.notifications.report.view'))
                    ->url(NotificationHelper::generateTenantUrl($this->report->tenant_id, 'filament.user.resources.reports.view', ['record' => $this->report->id])),
            ])
            ->viewData(['model_id' => $this->report->id, 'model_type' => Report::class])
            ->getDatabaseMessage();
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.report.view');

        if(!$preview){
            $reportUrl = NotificationHelper::generateTenantUrl($this->report?->tenant?->id, 'filament.user.resources.reports.view', ['record' => $this->report?->id]);
        }

        return [
            'titel' => $this->report->title ?? '',
            'omschrijving' => $this->report->description ?? '',
            'datum' => $this->report->appointment?->dt_start->format('d-m-Y') ?? '',
            ...$this->getTenantEmailInformation($this->report),
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $reportUrl ?? '#')
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'titel' => __('portal.notifications.report.params.title'),
            'omschrijving' => __('portal.notifications.report.params.description'),
            'datum' => __('portal.notifications.report.params.date'),
            ...self::getTenantEmailTemplateInformation(),
            'button' => __('portal.notifications.report.params.button')
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $notification = new self(Report::factory()::preview([
                'title' => 'Test titel',
                'description' => 'Test omschrijving',
            ]));
        });

        return $notification;
    }

    public function getReport(): Report
    {
        return $this->report;
    }
}
