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

class AppointmentCreated extends BaseAppointmentNotification implements ShouldQueue
{
    public function __construct(
        protected Appointment $appointment,
    )
    {
        $this->message = __('portal.notifications.appointment.created');
        parent::__construct($this->appointment);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::APPOINTMENT_CREATED->value;
    }

    public function via(object $notifiable): array
    {
        return NotificationHelper::getNotificationChannels($notifiable);
    }

    public function toDatabase($notifiable): array
    {
        return AppointmentEventHelper::toDatabase(
            $this->appointment,
            $notifiable,
            __('portal.notifications.appointment.subject'),
            __('portal.notifications.appointment.created_database', [
                'date' => $this->appointment->dt_start->translatedFormat('j F'),
                'time' => $this->appointment->dt_start->translatedFormat('g a')
            ])
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $address = $this->getLocationDetails();
        $description = $this->getDescription();

        $event = AppointmentEventHelper::createIcsEvent(
            appointment: $this->appointment,
            description: $description,
            address: $address,
            attendees: [$notifiable->email],
            organizer: $this->appointment->createdBy
        );

        $calendar = Calendar::create()
            ->event($event);

        $icalContent = $calendar->get();

        $url = url(route('filament.user.resources.appointments.view', ['record' => $this->appointment]));
        $parsedUrl = parse_url($url, PHP_URL_PATH);

        $appointmentUrl = "http://{$this->appointment->tenant->id}." . config('custom.central_domain') . $parsedUrl;

        $params = $this->templateParams($this->emailTemplate->button_text, $appointmentUrl, false);

        $emailContent = NotificationHelper::replaceVariables($this->emailContent, $params);

        return (new MailMessage)
            ->subject($this->emailTemplate->email_subject)
            ->markdown('emails.dynamic_notification', [
                'email_content' => $emailContent,
            ])
            ->attachData($icalContent, 'appointment.ics', [
                'mime' => 'text/calendar',
            ]);
    }

    public function templateParams($buttonText = null, $buttonUrl = '#', $preview = true): array
    {
        return AppointmentHelper::getTemplateParams($this->appointment, $this->appointment->location, $buttonText, $preview, $buttonUrl);
    }

    public static function templateParamsList(): array
    {
        return AppointmentHelper::getTemplateParamsList();
    }

    public function getAppointment(): Appointment
    {
        return $this->appointment;
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $appointment = AppointmentHelper::fake();
            $notification = new self(
                $appointment,
            );
        });

        return $notification;
    }
}
