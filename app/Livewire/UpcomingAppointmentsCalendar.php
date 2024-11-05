<?php

namespace App\Livewire;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Models\Feature;

class UpcomingAppointmentsCalendar extends BaseAppointmentsCalendar
{
    protected static string $view = 'livewire.upcoming-appointments-calendar';

    protected function queryUpcomingAppointments()
    {
        return parent::queryUpcomingAppointments()->whereHas('user');
    }

    public static function canView(): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && auth()->user()->hasTenantPermissionTo(Permissions::VIEW_UPCOMING_APPOINTMENTS_CALENDAR->value);
    }
}
