<?php

namespace App\Enums;

enum LocationOptions: string
{
    case MY_LOCATION = 'my_location';
    case USER_LOCATION = 'user_location';
    case ONLINE = 'online';
    case OTHER = 'other';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::MY_LOCATION => __('portal.appointments.my_location'),
            self::USER_LOCATION => __('portal.appointments.user_location'),
            self::ONLINE => __('portal.appointments.online'),
            self::OTHER => __('portal.appointments.other'),
            self::NONE => __('portal.appointments.none'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
