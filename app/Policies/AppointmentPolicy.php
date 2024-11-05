<?php

namespace App\Policies;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if($user->isUser()){
            return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::VIEW_USER_APPOINTMENTS->value);
        }

        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::VIEW_APPOINTMENTS->value);
    }

    public function create(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::CREATE_APPOINTMENTS->value);
    }

    public function update(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::EDIT_APPOINTMENTS->value);
    }

    public function delete(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::DELETE_APPOINTMENTS->value);
    }

    public function restore(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::EDIT_APPOINTMENTS->value);
    }

    public function viewUnbilled(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::VIEW_UNBILLED_APPOINTMENTS->value);
    }

    public function editUnbilled(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::EDIT_UNBILLED_APPOINTMENTS->value);
    }
}
