<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CallStatus: string implements HasLabel
{
    case ANSWERED = 'ANSWERED';
    case UNANSWERED = 'UNANSWERED';
    case BLACKLISTED = 'BLACKLISTED';
    case VOICEMAIL = 'VOICEMAIL';
    case CALL_CENTER = 'CALL_CENTER';
    case OUTSIDE_OPERATION_TIMES = 'OUTSIDE_OPERATION_TIMES';

    public function getLabel(): ?string
    {
        $lowercase = strtolower($this->value);
        return __("portal.calls.$lowercase");
    }
}
