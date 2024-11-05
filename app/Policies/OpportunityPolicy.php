<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpportunityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_OPPORTUNITIES->value);
    }

    public function view(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_OPPORTUNITIES->value);
    }

    public function create(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::CREATE_OPPORTUNITIES->value);
    }

    public function update(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_OPPORTUNITIES->value);
    }

    public function delete(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::DELETE_OPPORTUNITIES->value);
    }

    public function restore(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_OPPORTUNITIES->value);
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::DELETE_OPPORTUNITIES->value);
    }

    public function editKanban(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_OPPORTUNITIES_KANBAN_BOARD->value);
    }

    public function viewKanban(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_OPPORTUNITIES_KANBAN_BOARD->value);
    }

    public function createKanban(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::CREATE_OPPORTUNITIES_KANBAN_BOARD->value);
    }
}
