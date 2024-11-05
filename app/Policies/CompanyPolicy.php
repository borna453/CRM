<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_COMPANIES->value);
    }

    public function view(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_COMPANIES->value);
    }

    public function create(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::CREATE_COMPANIES->value);
    }

    public function update(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_COMPANIES->value);
    }

    public function delete(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::DELETE_COMPANIES->value);
    }

    public function restore(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::RESTORE_COMPANIES->value);
    }
}
