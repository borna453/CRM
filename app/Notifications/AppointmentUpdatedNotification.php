<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\Appointment;
use App\Utils\Notifications\AppointmentEventHelper;
use App\Utils\Notifications\AppointmentHelper;
use App\Utils\Notifications\NotificationHelper;
use App\Utils\UniqueIdentifierHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Properties\TextProperty;

class AppointmentUpdatedNotification extends BaseAppointmentNotification implements ShouldQueue
{
    public function __construct(
        protected Appointment $appointment, private readonly Carbon $originalStart
    )
    {
        $this->message = __('portal.notifications.appointment.updated', ['date' => $this->appointment->dt_start->translatedFormat('j F'), 'time' => $this->appointment->dt_start->translatedFormat('g a')]);
        parent::__construct($this->appointment);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::APPOINTMENT_UPDATED->value;
    }

    public function via($notifiable): array
    {
        return NotificationHelper::getNotificationChannels($notifiable);
    }

    public function toDatabase($notifiable): array
    {
        return AppointmentEventHelper::toDatabase(
            $this->appointment,
            $notifiable,
            __('portal.notifications.appointment.subject_updated'),
            $this->message);
    }

    public function toMail($notifiable): MailMessage
    {
        $address = $this->getLocationDetails();
        $description = $this->getDescription();

        // Create the cancellation event with the same UID
        $cancelEvent = AppointmentEventHelper::createCancellationEvent(
            $this->appointment,
            $description,
            $address,
            [$notifiable->email]
        )->uniqueIdentifier(UniqueIdentifierHelper::getId($this->appointment->id).':'.$this->originalStart->getTimestamp());

        // Create the updated event
        $updateEvent = AppointmentEventHelper::createIcsEvent(
            appointment: $this->appointment,
            description: $description,
            address: $address,
            attendees: [$notifiable->email],
            organizer: $this->appointment->createdBy
        );

        $calendar = Calendar::create()
            ->event($cancelEvent->appendProperty(TextProperty::create('METHOD', 'CANCEL')))
            ->event($updateEvent);

        $icalContent = $calendar->get();

        $url = url(route('filament.user.resources.appointments.view', ['record' => $this->appointment]));
        $parsedUrl = parse_url($url, PHP_URL_PATH);

        $appointmentUrl = "http://{$this->appointment->tenant->id}." . config('custom.central_domain') .$parsedUrl;

        $params = $this->templateParams($this->emailTemplate->button_text, false, $appointmentUrl);

        $emailContent = NotificationHelper::replaceVariables($this->emailContent, $params);

        return (new MailMessage)
            ->greeting(__('portal.notifications.greeting'))
            ->subject($this->emailTemplate->email_subject)
            ->markdown('emails.dynamic_notification', [
                'email_content' => $emailContent,
            ])
            ->attachData($icalContent, 'appointment_update.ics', [
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

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification){
            $appointment = AppointmentHelper::fake();
            $notification = new self(
                $appointment,
                $appointment->dt_start->subDay()
            );
        });

        return $notification;
    }
}
