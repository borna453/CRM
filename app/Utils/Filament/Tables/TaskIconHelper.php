<?php

namespace App\Utils\Filament\Tables;

class TaskIconHelper
{
    public static function getIcon($record): ?string
    {
        if ($record->dt_complete_by && !$record->dt_is_completed && is_null($record->deleted_at)) {
            return match (true) {
                $record->dt_complete_by->isPast() && !$record->dt_complete_by->isToday() => 'heroicon-s-exclamation-triangle',
                $record->dt_complete_by->isToday() => 'heroicon-s-clock',
                default => null,
            };
        }

        return null;
    }

    public static function getIconColor($record): ?string
    {
        if ($record->dt_complete_by && !$record->dt_is_completed && is_null($record->deleted_at)) {
            return match (true) {
                $record->dt_complete_by->isPast() && !$record->dt_complete_by->isToday() => 'red',
                $record->dt_complete_by->isToday() => 'orange',
                default => null,
            };
        }

        return null;
    }
}
