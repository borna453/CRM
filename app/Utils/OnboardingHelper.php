<?php

namespace App\Utils;

use App\Enums\OnboardingTypes;
use App\Models\Onboarding;

class OnboardingHelper
{
    public static function completeStep(OnboardingTypes $currentStep, callable $condition): bool
    {
        $onboarding = Onboarding::where('tenant_id', tenant()->id)
            ->where('step', $currentStep->value)
            ->first();

        if ($onboarding && !$onboarding->is_complete && $condition()) {
            $onboarding->update(['is_complete' => true]);
            return true;
        }

        return $onboarding && $onboarding->is_complete;
    }
}
