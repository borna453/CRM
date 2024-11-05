<?php

namespace App\Enums;

enum Features: string
{
    case APPOINTMENTS_AND_REPORTS = 'appointments_and_reports';
    case ADMINISTRATION = 'administration';
    case RINKEL = 'rinkel';

    public function translate(): string
    {
        return match ($this) {
            self::APPOINTMENTS_AND_REPORTS => __('portal.appointments_and_reports'),
            self::ADMINISTRATION => __('portal.administration'),
            self::RINKEL => 'Rinkel',
        };
    }

    public static function getLabel(string $name): string
    {
        return self::from($name)->translate();
    }
}
