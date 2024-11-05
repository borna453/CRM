<?php

namespace App\Policies;

use App\Enums\Features;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Enums\Permissions;

class ReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if($user->isUser()){
            return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::VIEW_USER_REPORTS->value);
        }

        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::VIEW_REPORTS->value);
    }

    public function create(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::CREATE_REPORTS->value);
    }

    public function update(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::EDIT_REPORTS->value);
    }

    public function delete(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::DELETE_REPORTS->value);
    }

    public function restore(User $user): bool
    {
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::EDIT_REPORTS->value);
    }

    public function createComment(User $user): bool
    {
        if($user->isUser()){
            return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::USER_ADD_COMMENTS_TO_REPORTS->value);
        }
        return Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && $user->hasTenantPermissionTo(Permissions::ADD_COMMENTS_TO_REPORTS->value);
    }
}
