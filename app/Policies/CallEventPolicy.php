<?php

namespace App\Policies;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Models\CallEvent;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CallEventPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_CALL_EVENTS->value) && Feature::isActive(Features::RINKEL);
    }
}
