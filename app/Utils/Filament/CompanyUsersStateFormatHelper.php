<?php

namespace App\Utils\Filament;

use Illuminate\Support\HtmlString;

class CompanyUsersStateFormatHelper
{
    public static function formatBooleanState(bool $state, string $label): HtmlString
    {
        $icon = $state
            ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';

        return new HtmlString('<span class="font-semibold">' . $label . ':</span> ' . $icon);
    }
}
