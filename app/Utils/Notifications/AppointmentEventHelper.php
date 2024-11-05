<?php

namespace App\Utils\Notifications;

use App\Models\Appointment;
use App\Models\User;
use App\Utils\UniqueIdentifierHelper;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Str;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\EventStatus;
use Spatie\IcalendarGenerator\Properties\TextProperty;

class AppointmentEventHelper
{
    public static function toDatabase($appointment, User $notifiable, $title, $bodyMessage): array
    {
        $baseRoute = $notifiable->isAdmin() ? 'filament.admin.resources.appointments.view' : 'filament.user.resources.appointments.view';

        $url = NotificationHelper::generateTenantUrl($appointment->tenant_id, $baseRoute, ['record' => $appointment]);

        return \Filament\Notifications\Notification::make()
            ->title($title)
            ->body($bodyMessage . " {$appointment->title}.")
            ->actions([
                Action::make('openAppointment')
                    ->label(__('portal.appointments.view_appointment'))
                    ->url($url)
                    ->markAsRead()
            ])
            ->viewData(['model_id' => $appointment->id, 'model_type' => Appointment::class])
            ->getDatabaseMessage();
    }

    public static function getLocationDetails($appointment, $location)
    {
        switch ($location) {
            case 'my_location':
                return self::getAdminCompanyDetails();
            case 'user_location':
                return self::getUserCompanyDetails($appointment);
            case 'online':
                return __('portal.appointments.online');
            case 'other':
                return $appointment->other_location;
            case 'none':
            default:
                return '';
        }
    }

    public static function getCompanyDetails($company): string
    {
        if ($company) {
            return implode(', ', array_filter([
                $company->name,
                $company->address,
                Str::upper($company->zip_code),
                $company->city
            ]));
        }
        return '';
    }

    public static function getAdminCompanyDetails(): string
    {
        $admin = auth()->user();
        return $admin && $admin->company ? self::getCompanyDetails($admin->company) : '';
    }

    public static function getUserCompanyDetails($appointment): string
    {
        return $appointment && $appointment->user && $appointment->user->company
            ? self::getCompanyDetails($appointment->user->company) : '';
    }

    public static function createIcsEvent(Appointment $appointment, $description, $address, $attendees, $organizer, $status = null)
    {
        $event = Event::create()
            ->name($appointment->title)
            ->description($description)
            ->startsAt($appointment->dt_start->timezone('UTC'))
            ->endsAt($appointment->dt_end->timezone('UTC'))
            ->address($address)
            ->organizer($organizer)
            ->uniqueIdentifier(UniqueIdentifierHelper::getId($appointment->id).':'.$appointment->dt_start->getTimestamp());

        foreach ($attendees as $attendee) {
            $event->attendee($attendee);
        }

        if ($status) {
            $event->status($status);
        }

        return $event;
    }

    public static function createCancellationEvent(Appointment $appointment, $description, $address, $attendees)
    {
        return self::createIcsEvent($appointment, $description, $address, $attendees, EventStatus::cancelled());
    }
}
