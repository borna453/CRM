<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_NOTES->value);
    }

    public function view(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_NOTES->value);
    }

    public function create(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::CREATE_NOTES->value);
    }

    public function update(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::EDIT_NOTES->value);
    }

    public function delete(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::DELETE_NOTES->value);
    }
}
