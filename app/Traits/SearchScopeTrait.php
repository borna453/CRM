<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchScopeTrait
{
    public function scopeSearch(Builder $query, string $searchTerm, string $column, ?string $additionalColumn = null): Builder
    {
        return $query->where(function ($query) use ($searchTerm, $column, $additionalColumn) {
            $query->where($column, 'like', '%'.$searchTerm.'%');

            if (! is_null($additionalColumn)) {
                $query->orWhere($additionalColumn, 'like', '%'.$searchTerm.'%');
            }
        });
    }
}
