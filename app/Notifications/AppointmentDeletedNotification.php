<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\Appointment;
use App\Utils\Notifications\AppointmentEventHelper;
use App\Utils\Notifications\AppointmentHelper;
use App\Utils\Notifications\NotificationHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Properties\TextProperty;

class AppointmentDeletedNotification extends BaseAppointmentNotification implements ShouldQueue
{

    public function __construct(protected Appointment $appointment)
    {
        $this->message = __('portal.notifications.appointment.deleted');
        parent::__construct($this->appointment);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::APPOINTMENT_CANCELED->value;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $address = $this->getLocationDetails();
        $description = $this->getDescription();

        $cancelEvent = AppointmentEventHelper::createCancellationEvent(
            $this->appointment,
            $description,
            '',
            [$notifiable->email]
        );

        $calendar = Calendar::create()
            ->event($cancelEvent)
            ->appendProperty(TextProperty::create('method', 'CANCEL'));

        $icalContent = $calendar->get();

        $params = $this->templateParams($this->emailTemplate->button_text, false);

        $emailContent = NotificationHelper::replaceVariables($this->emailContent, $params);

        return (new MailMessage)
            ->subject($this->emailTemplate->email_subject)
            ->markdown('emails.dynamic_notification', [
                'email_content' => $emailContent,
            ])
            ->attachData($icalContent, __('portal.notifications.appointment.attachment'), [
                'mime' => 'text/calendar',
            ]);
    }

    public function templateParams($buttonText = null, $preview = true): array
    {
        $params = AppointmentHelper::getTemplateParams($this->appointment, $this->appointment->location, $buttonText, $preview);
        unset($params['button']);
        return $params;
    }

    public static function templateParamsList(): array
    {
        $paramsList = AppointmentHelper::getTemplateParamsList();
        unset($paramsList['button']);
        return $paramsList;
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $notification = new self(AppointmentHelper::fake());
        });

        return $notification;
    }
}
