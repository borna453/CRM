<?php

namespace App\Utils;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

class BrandLogoHelper
{
    public static function brandLogo(): ?string
    {
        if ($logo = tenant()?->general['logo_light_mode'] ?? null) {
            return Storage::url($logo);
        }

        if (app()->runningInConsole()) {
            return null;
        }

        return Vite::asset('resources/images/cloudmazing-software-logo-transparent.png');
    }

    public static function darkModeBrandLogo(): ?string
    {
        if ($logo = tenant()?->general['logo_dark_mode'] ?? null) {
            return Storage::url($logo);
        }

        if (app()->runningInConsole()) {
            return null;
        }

        return Vite::asset('resources/images/cloudmazing-software-logo-black-transparent.png');
    }

    public static function favicon(): ?string
    {
        if ($favicon = tenant()?->general['favicon'] ?? null) {
            return Storage::url($favicon);
        }

        if (app()->runningInConsole()) {
            return null;
        }

        return Vite::asset('resources/images/cloudmazing-favicon-transparent.ico');
    }
}
