<?php

namespace App\Utils;

use Carbon\Carbon;

class AppointmentDateHelper
{
    public static function formatDateRange($record): string
    {
        $start = $record->dt_start->format('d-m-Y H:i');
        $end = $record->dt_end->format('H:i');
        return $start . ' - ' . $end . ' uur';
    }
}
