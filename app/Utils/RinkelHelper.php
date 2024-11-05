<?php

namespace App\Utils;

use App\Models\Company;
use App\Models\User;

class RinkelHelper
{
    public static function findTenantId(): ?string
    {
        return explode('.', \Request::getHost())[0];
    }

    public static function findCompanyId($number): int | null
    {
        return Company::where('phone_number', $number)->first()?->id;
    }

    public static function findAnsweredBy($email): int | null
    {
        return User::where('email', $email)->first()?->id;
    }
}
