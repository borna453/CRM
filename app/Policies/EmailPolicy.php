<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Email;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasTenantPermissionTo(Permissions::VIEW_EMAIL_LOGS->value) && $user->isSuperAdmin();
    }
}
