<?php

namespace App\Traits;

use App\Models\User;

trait ScopeWhereVisibleToTrait
{
    public function scopeWhereVisibleTo($query, User $user)
    {
        if ($user->isUser()) {
            return $query->where('user_id', auth()->id());
        }

        return $query;
    }
}
