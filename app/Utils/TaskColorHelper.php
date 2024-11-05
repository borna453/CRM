<?php

namespace App\Utils;

use App\Models\Task;
use Carbon\Carbon;

class TaskColorHelper
{
    static function getTaskRowClasses(Task $record)
    {
        $rowDate = Carbon::parse($record->dt_complete_by);

        if ($record->dt_complete_by && !$record->dt_is_completed && is_null($record->deleted_at)) {
            return [
                'bg-red-100 dark:bg-red-600' => $rowDate->isPast() && !$rowDate->isToday(),
                'bg-orange-100 dark:bg-orange-500' => $rowDate->isToday(),
            ];
        }

        return null;
    }
}
