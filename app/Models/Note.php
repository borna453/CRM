<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Observers\NotesObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

#[ObservedBy(NotesObserver::class)]
/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $note
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent|null $model
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Note newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Note newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Note onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Note query()
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Note withoutTrashed()
 * @mixin \Eloquent
 */
class Note extends Model implements TenantContract
{
    use SoftDeletes;
    use BelongsToTenant;

    protected $guarded = [];

    public function model(): MorphTo
    {
        return $this->morphTo()->with('user');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
