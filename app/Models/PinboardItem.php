<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Observers\PinboardItemObserver;
use App\Traits\ScopeWhereVisibleToTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int|null $user_id
 * @property int|null $created_by
 * @property string|null $description
 * @property string|null $dt_is_completed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \App\Models\User|null $user
 * @method static Builder|PinboardItem completed()
 * @method static \Database\Factories\PinboardItemFactory factory($count = null, $state = [])
 * @method static Builder|PinboardItem newModelQuery()
 * @method static Builder|PinboardItem newQuery()
 * @method static Builder|PinboardItem onlyTrashed()
 * @method static Builder|PinboardItem open()
 * @method static Builder|PinboardItem query()
 * @method static Builder|PinboardItem whereCreatedAt($value)
 * @method static Builder|PinboardItem whereCreatedBy($value)
 * @method static Builder|PinboardItem whereDeletedAt($value)
 * @method static Builder|PinboardItem whereDescription($value)
 * @method static Builder|PinboardItem whereDtIsCompleted($value)
 * @method static Builder|PinboardItem whereId($value)
 * @method static Builder|PinboardItem whereTenantId($value)
 * @method static Builder|PinboardItem whereUpdatedAt($value)
 * @method static Builder|PinboardItem whereUserId($value)
 * @method static Builder|PinboardItem whereVisibleTo(\App\Models\User $user)
 * @method static Builder|PinboardItem withTrashed()
 * @method static Builder|PinboardItem withoutTrashed()
 * @mixin \Eloquent
 */

#[ObservedBy([PinboardItemObserver::class])]
class PinboardItem extends Model implements TenantContract
{
    use HasFactory;
    use SoftDeletes;
    use BelongsToTenant;
    use ScopeWhereVisibleToTrait;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function complete()
    {
        $this->update(['dt_is_completed' => Carbon::now()]);
    }

    public function uncomplete()
    {
        $this->update(['dt_is_completed' => null]);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('dt_is_completed');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('dt_is_completed');
    }
}
