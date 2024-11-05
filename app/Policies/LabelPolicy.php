<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Label;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabelPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_LABELS->value);
    }

    public function view(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_LABELS->value);
    }

    public function create(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::CREATE_LABELS->value);
    }

    public function update(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_LABELS->value);
    }

    public function delete(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::DELETE_LABELS->value);
    }

    public function reorder(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::REORDER_LABELS->value);
    }
}
