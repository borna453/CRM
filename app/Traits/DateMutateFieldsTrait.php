<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateMutateFieldsTrait
{
    public static function combineDateFields(array $data): array
    {
        $adminTimezone = auth()->user()->timezone;

        $date = Carbon::createFromFormat('Y-m-d', $data['date'], $adminTimezone);
        $startDateTime = $date->clone()->setTimeFromTimeString($data['start_time']);
        $endDateTime = $date->clone()->setTimeFromTimeString($data['end_time']);

        $data['dt_start'] = $startDateTime->copy()->setTimezone('UTC')->toDateTimeString();
        $data['dt_end'] = $endDateTime->copy()->setTimezone('UTC')->toDateTimeString();

        unset($data['date'], $data['start_time'], $data['end_time']);

        return $data;
    }
}
