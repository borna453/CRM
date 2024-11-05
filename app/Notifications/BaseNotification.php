<?php

namespace App\Notifications;

use App\Models\Contracts\TenantContract;
use App\Models\NotificationTemplate;
use App\Traits\GetEmailContentTrait;
use App\Traits\SafelyFakeNotificationTrait;
use App\Utils\Notifications\NotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    use Queueable;
    use GetEmailContentTrait;
    use SafelyFakeNotificationTrait;

    protected string $message;
    protected string $emailContent;
    protected NotificationTemplate $emailTemplate;
    protected array $params;

    public function __construct(array $params)
    {
        $this->params = $params;

        $this->initialize();
    }

    protected function initialize(): void
    {
        $this->setEmailTemplate();

        $this->emailContent = $this->emailTemplate->email_content;
    }

    protected function setEmailTemplate(): void
    {
        $this->emailTemplate = $this->getEmailModel($this->getType());
    }

    abstract protected function getType(): string;

    public function toMail(object $notifiable): MailMessage
    {
        $emailContent = $this->replaceVariables($this->emailContent, $this->templateParams());

        return (new MailMessage)
            ->subject($this->emailTemplate->email_subject)
            ->markdown('emails.dynamic_notification', [
                'email_content' => $emailContent,
            ]);
    }

    abstract public function templateParams(): array;

    protected function replaceVariables(string $content, array $params): string
    {
        return NotificationHelper::replaceVariables($content, $params);
    }

    protected function getTenantEmailInformation(TenantContract $model): array
    {
        //html_entity_decode(strip_tags(auth()->user()?->tenant?->email['footer'], '<br>')) ;
        $tenant = $model->tenant ?? auth()->user()?->tenant;
        $tenantEmailFrom = $tenant->email['from_name'] ?? str_replace('-', ' ', ucwords($tenant?->id ?? env('APP_NAME'))) ;
        $tenantEmailFooter = $tenant->email['footer'] ?? '';

        return [
            'tenant_email_from' => $tenantEmailFrom,
            'tenant_email_footer' => $tenantEmailFooter,
        ];
    }

    public static function getTenantEmailTemplateInformation(): array
    {
        return [
            'tenant_email_from' => __('portal.notifications.params.tenant_email_from'),
            'tenant_email_footer' => __('portal.notifications.params.tenant_email_footer'),
        ];
    }

    abstract public static function fake(): Notification;
}
