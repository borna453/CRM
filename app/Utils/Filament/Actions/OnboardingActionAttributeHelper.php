<?php

namespace App\Utils\Filament\Actions;

class OnboardingActionAttributeHelper
{
    public static function glow($type): string
    {
        return \Request::get("onboard_$type") === '1' ? 'glowing-light' : '';
    }
}
