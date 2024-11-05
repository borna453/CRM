<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Task;
use App\Utils\AppointmentReportTasksHelper;
use App\Utils\Livewire\TaskActionHelper;
use Livewire\Component;

class CustomerAppointments extends Component
{
    public $userId;

    public function render()
    {
        $appointments = Appointment::where('user_id', $this->userId)
            ->with(['report', 'report.tasks', 'tasks', 'report.appointment', 'report.appointment.tasks'])
            ->orderBy('dt_start', 'asc')
            ->get();

        foreach ($appointments as $appointment) {
            if ($appointment->report) {
                $appointment->report->sortedTasks = AppointmentReportTasksHelper::getSortedTasks($appointment->report);
            }
        }

        return view('livewire.customer-appointments', ['appointments' => $appointments]);
    }

    public function toggleTaskCompletion($taskId)
    {
        TaskActionHelper::toggleTaskCompletion($taskId, $this);
    }
}
