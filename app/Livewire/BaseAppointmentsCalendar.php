<?php

namespace App\Livewire;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class BaseAppointmentsCalendar extends Widget
{
    protected static string $view;

    public $allUpcomingAppointmentDates;

    public function mount()
    {
        $this->load();
    }

    #[On('reload')]
    public function load(): void
    {
        $this->allUpcomingAppointmentDates = $this->getAllUpcomingAppointmentDates();
    }

    public function getUpcomingAppointmentsProperty()
    {
        $appointments = $this->queryUpcomingAppointments()
            ->with('user')
            ->orderBy('dt_start', 'asc')
            ->get();

        return $appointments->groupBy(function ($appointment) {
            return $this->dateHeading($appointment);
        });
    }

    protected function dateHeading(Appointment $appointment): string
    {
        $start = $appointment->dt_start;

        return match (true) {
            $start->isToday() => __('portal.today'),
            $start->isTomorrow() => __('portal.tomorrow'),
            default => $start->format('d-m-Y'),
        };
    }

    protected function getAllUpcomingAppointmentDates()
    {
        return $this->queryUpcomingAppointments()
            ->pluck('dt_start')
            ->map(fn (Carbon $datetime) => $datetime->getTimestampMs())
            ->toArray();
    }

    protected function queryUpcomingAppointments()
    {
        return Appointment::whereUpcoming();
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            '2xl' => 1,
            'xl' => 2,
            'lg' => 2,
            'md' => 2,
            'sm' => 2,
        ];
    }
}
