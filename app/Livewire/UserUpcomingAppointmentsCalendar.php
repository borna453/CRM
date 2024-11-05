<?php

namespace App\Livewire;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Models\Feature;

class UserUpcomingAppointmentsCalendar extends BaseAppointmentsCalendar
{
    protected static string $view = 'livewire.user-upcoming-appointments-calendar';

    protected function queryUpcomingAppointments()
    {
        return parent::queryUpcomingAppointments()->where('user_id', auth()->id());
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            'md' => 2,
            'lg' => 2,
            'xl' => 2,
            '2xl' => 1,
        ];
    }

    public static function canView(): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && auth()->user()->hasTenantPermissionTo(Permissions::VIEW_USER_UPCOMING_APPOINTMENTS_CALENDAR->value);
    }
}
