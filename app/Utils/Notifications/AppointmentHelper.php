<?php

namespace App\Utils\Notifications;

use App\Models\Appointment;
use App\Models\Report;
use App\Models\User;
use App\Traits\SafelyFakeNotificationTrait;
use Illuminate\Support\Facades\DB;

class AppointmentHelper
{
    use SafelyFakeNotificationTrait;
    public static function getTemplateParamsList(): array
    {
        return [
            'datum' => __('portal.notifications.appointment.parameters.date'),
            'tijd' => __('portal.notifications.appointment.parameters.time'),
            'organizer_naam' => __('portal.notifications.appointment.parameters.organizer_name'),
            'titel' => __('portal.notifications.appointment.parameters.title'),
            'omschrijving' => __('portal.notifications.appointment.parameters.description'),
            'locatie' => __('portal.notifications.appointment.parameters.location'),
            'button' => __('portal.notifications.appointment.parameters.button'),
            'online_url' => __('portal.notifications.appointment.parameters.online_url'),
            'tenant_email_from' => __('portal.notifications.params.tenant_email_from'),
            'tenant_email_footer' => __('portal.notifications.params.tenant_email_footer'),
        ];
    }

    public static function getTemplateParams(Appointment $appointment, $location, $buttonText = null, $preview = true, $appointmentUrl = '#'): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.appointment.view');

        if ($preview) {
            $address = AppointmentEventHelper::getUserCompanyDetails($appointment);
        } else {
            $address = AppointmentEventHelper::getLocationDetails($appointment, $location);
        }

        return [
            'datum' => $appointment->dt_start->timezone($appointment->user->timezone)->translatedFormat('j F') ?? '',
            'tijd' => $appointment->dt_start->timezone($appointment->user->timezone)->translatedFormat('g:i A') ?? '',
            'organizer_naam' => $appointment->createdBy?->name ?? '',
            'titel' => $appointment->title ?? '',
            'omschrijving' => $appointment->description ?? '',
            'locatie' => $address ?? '',
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $appointmentUrl),
            'online_url' => $appointment->online_url ?? '',
            'tenant_email_from' => auth()->user()?->tenant?->email['from_name'] ?? ucwords(tenant()?->id),
            'tenant_email_footer' => auth()->user()?->tenant?->email['footer'] ?? '',
        ];
    }

    public static function fake()
    {

        self::safelyFake(function () use (&$appointment) {
            $report = Report::factory()->make([
                'title' => 'Maandelijkse rapportage',
                'description' => 'Dit is de maandelijkse rapportage over de voortgang van het project.',
            ]);

            $user = User::factory()->make([
                'first_name' => 'Jan',
                'last_name' => 'Jansen',
            ]);

            $createdBy = User::factory()->make([
                'first_name' => 'Piet',
                'last_name' => 'Pietersen'
            ]);
            $appointment = Appointment::factory()::preview([
                'title' => 'Zakelijke Bespreking',
                'location' => 'my_location',
                'online_url' => 'https://voorbeeld.com/online-vergadering',
                'description' => 'Dit is een zakelijke bespreking over het nieuwe project en de aanstaande deadlines.',
                'dt_start' => now(),
                'dt_end' => now()->addHour(),
            ], $report, $user, $createdBy);
        });

        DB::rollBack();

        return $appointment;
    }
}
