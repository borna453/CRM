<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

class TaskPolicy
{
    public function viewAny(User $user)
    {
        if($user->isUser()){
            return $user->hasTenantPermissionTo(Permissions::VIEW_USER_TASKS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::VIEW_TASKS->value);
    }

    public function create(User $user)
    {
        if ($user->isUser()) {
            return $user->hasTenantPermissionTo(Permissions::CREATE_USER_TASKS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::CREATE_TASKS->value);
    }

    public function update(User $user)
    {
        if ($user->isUser()) {
            return $user->hasTenantPermissionTo(Permissions::EDIT_USER_TASKS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::EDIT_TASKS->value);
    }

    public function delete(User $user)
    {
        if ($user->isUser()) {
            return $user->hasTenantPermissionTo(Permissions::DELETE_USER_TASKS->value);
        }

        return $user->hasTenantPermissionTo(Permissions::DELETE_TASKS->value);
    }

    public function restore(User $user)
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_TASKS->value);
    }

    public function forceDelete(User $user)
    {
        return $user->hasTenantPermissionTo(Permissions::DELETE_TASKS->value);
    }
}
