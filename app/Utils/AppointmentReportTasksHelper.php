<?php

namespace App\Utils;

class AppointmentReportTasksHelper
{
    public static function getSortedTasks($report)
    {
        $tasks = $report->tasks;

        if ($report->appointment) {
            $tasks = $tasks->merge($report->appointment->tasks);
        }

        return $tasks->sortBy(function ($task) {
            return $task->dt_complete_by ? $task->dt_complete_by->timestamp : PHP_INT_MAX;
        });
    }
}
