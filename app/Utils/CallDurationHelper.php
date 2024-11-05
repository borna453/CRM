<?php

namespace App\Utils;

class CallDurationHelper
{
    public static function formatDuration($record)
    {
        $seconds = $record->duration;
        $formattedDuration = '';

        // Calculate hours, minutes, and seconds
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            $formattedDuration .= $hours . 'h ';
        }
        if ($minutes > 0) {
            $formattedDuration .= $minutes . 'm ';
        }
        $formattedDuration .= $seconds . 's';

        return trim($formattedDuration);
    }
}
