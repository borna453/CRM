<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin() || $user->isOwner()){
            return true;
        }

        return $user->hasTenantPermissionTo(Permissions::VIEW_USERS->value);
    }

    public function view(User $user): bool
    {
        if($user->isSuperAdmin() || $user->isOwner()){
            return true;
        }

        return $user->hasTenantPermissionTo(Permissions::VIEW_USERS->value);
    }

    public function create(User $user): bool
    {
        if($user->isSuperAdmin() || $user->isOwner()){
            return true;
        }

        return $user->hasTenantPermissionTo(Permissions::CREATE_USERS->value);
    }

    public function update(User $user): bool
    {
        if($user->isSuperAdmin() || $user->isOwner()){
            return true;
        }

        return $user->hasTenantPermissionTo(Permissions::EDIT_USERS->value);
    }

    public function delete(User $user): bool
    {
        if($user->isSuperAdmin() || $user->isOwner()){
            return true;
        }

        return $user->hasTenantPermissionTo(Permissions::DELETE_USERS->value);
    }

    public function restore(User $user): bool
    {
        if($user->isSuperAdmin() || $user->isOwner()){
            return true;
        }

        return $user->hasTenantPermissionTo(Permissions::RESTORE_USERS->value);
    }

    public function impersonate(User $user): bool
    {
        if($user->isSuperAdmin() || $user->isOwner()){
            return true;
        }

        return $user->hasTenantPermissionTo(Permissions::IMPERSONATE_USERS->value);
    }
}
