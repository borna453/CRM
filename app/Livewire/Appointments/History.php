<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Report;
use App\Models\Task;
use App\Utils\AppointmentReportTasksHelper;
use App\Utils\Livewire\TaskActionHelper;
use Livewire\Attributes\On;
use Livewire\Component;

class History extends Component
{
    public $userId;
    public $currentReportId;

    public function render()
    {
        $reports = Report::where('user_id', $this->userId)
            ->when($this->currentReportId, function ($query) {
                return $query->where('id', '!=', $this->currentReportId);
            })
            ->wherePast()
            ->whereHas('appointment', function ($query) {
                $query->orderBy('dt_start', 'desc');
            })
            ->with(['tasks', 'appointment', 'appointment.tasks'])
            ->get();

        foreach ($reports as $report) {
            $report->sortedTasks = AppointmentReportTasksHelper::getSortedTasks($report);
        }

        return view('livewire.appointments.history', ['reports' => $reports]);
    }

    public function toggleTaskCompletion($taskId)
    {
        TaskActionHelper::toggleTaskCompletion($taskId, $this);
    }
}
