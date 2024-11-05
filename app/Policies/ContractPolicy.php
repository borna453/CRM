<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_CONTRACTS->value);
    }

    public function view(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_CONTRACTS->value);
    }

    public function create(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::CREATE_CONTRACTS->value);
    }

    public function update(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_CONTRACTS->value);
    }

    public function delete(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::DELETE_CONTRACTS->value);
    }
}
