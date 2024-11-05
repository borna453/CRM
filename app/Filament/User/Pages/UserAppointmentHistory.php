<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Resources\AppointmentResource;
use App\Models\Report;
use App\Utils\AppointmentReportTasksHelper;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class UserAppointmentHistory extends Page
{
    protected static string $view = 'filament.user.pages.user-appointment-history';

    protected static bool $shouldRegisterNavigation = false;


    protected function getViewData(): array
    {
        $reports = Report::where('user_id', auth()->id())
            ->published()
            ->wherePast()
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($reports as $report) {
            $report->sortedTasks = AppointmentReportTasksHelper::getSortedTasks($report);
        }

        return ['reports' => $reports];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.user.resources.appointments.index') => __('portal.appointments.appointments'),
            route('filament.user.pages.user-appointment-history') => __('portal.appointments.history'),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return __('portal.appointments.history');
    }
}
