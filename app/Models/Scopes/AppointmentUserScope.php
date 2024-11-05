<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AppointmentUserScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('user', function (Builder $query) {
            $query->whereNotNull('user_id');
        });
    }
}
