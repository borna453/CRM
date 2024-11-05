<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PinboardItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->isUser()) {
            return $user->hasTenantPermissionTo(Permissions::VIEW_USER_PINBOARD_ITEMS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::VIEW_PINBOARD_ITEMS->value);
    }

    public function create(User $user): bool
    {
        if($user->isUser()){
            return $user->hasTenantPermissionTo(Permissions::CREATE_USER_PINBOARD_ITEMS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::CREATE_PINBOARD_ITEMS->value);
    }

    public function update(User $user): bool
    {
        if($user->isUser()){
            return $user->hasTenantPermissionTo(Permissions::EDIT_USER_PINBOARD_ITEMS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::EDIT_PINBOARD_ITEMS->value);
    }

    public function delete(User $user): bool
    {
        if($user->isUser()){
            return $user->hasTenantPermissionTo(Permissions::DELETE_USER_PINBOARD_ITEMS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::DELETE_PINBOARD->value);
    }
}
