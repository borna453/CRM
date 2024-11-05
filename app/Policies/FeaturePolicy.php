<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeaturePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->hasTenantPermissionTo(Permissions::VIEW_FEATURE_SETTINGS->value);
    }

    public function view(User $user): bool
    {
        return $user->isOwner() || $user->hasTenantPermissionTo(Permissions::VIEW_FEATURE_SETTINGS->value);
    }

    public function update(User $user): bool
    {
        return $user->isOwner() || $user->hasTenantPermissionTo(Permissions::EDIT_FEATURE_SETTINGS->value);
    }
}
