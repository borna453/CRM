<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if($user->isUser()){
            return $user->hasTenantPermissionTo(Permissions::VIEW_USER_MESSAGE_RESOURCES->value);
        }

        return $user->hasTenantPermissionTo(Permissions::VIEW_MESSAGES->value);
    }

    public function create(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::CREATE_MESSAGES->value);
    }
}
