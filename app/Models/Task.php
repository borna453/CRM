<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Observers\TaskObserver;
use App\Traits\ScopeWhereVisibleToTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $title
 * @property string|null $information
 * @property int|null $user_id
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $dt_complete_by
 * @property string|null $dt_is_completed
 * @property int|null $is_private
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|\Eloquent|null $model
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \App\Models\User|null $user
 * @method static Builder|Task associatedWithModel($appointmentId, $reportId)
 * @method static Builder|Task completed()
 * @method static Builder|Task createdBy(\App\Models\User $user)
 * @method static Builder|Task due()
 * @method static Builder|Task dueToday()
 * @method static \Database\Factories\TaskFactory factory($count = null, $state = [])
 * @method static Builder|Task modelType(string $modelType)
 * @method static Builder|Task newModelQuery()
 * @method static Builder|Task newQuery()
 * @method static Builder|Task onlyTrashed()
 * @method static Builder|Task open()
 * @method static Builder|Task orderByDeadlineDate()
 * @method static Builder|Task query()
 * @method static Builder|Task whereAssignedTo(\App\Models\User $user)
 * @method static Builder|Task whereCreatedAt($value)
 * @method static Builder|Task whereCreatedBy($value)
 * @method static Builder|Task whereDeletedAt($value)
 * @method static Builder|Task whereDtCompleteBy($value)
 * @method static Builder|Task whereDtIsCompleted($value)
 * @method static Builder|Task whereId($value)
 * @method static Builder|Task whereInformation($value)
 * @method static Builder|Task whereIsPrivate($value)
 * @method static Builder|Task whereModelId($value)
 * @method static Builder|Task whereModelType($value)
 * @method static Builder|Task whereTenantId($value)
 * @method static Builder|Task whereTitle($value)
 * @method static Builder|Task whereUpdatedAt($value)
 * @method static Builder|Task whereUserId($value)
 * @method static Builder|Task whereVisibleTo(\App\Models\User $user)
 * @method static Builder|Task withTrashed()
 * @method static Builder|Task withoutTrashed()
 * @mixin \Eloquent
 */

#[ObservedBy([TaskObserver::class])]
class Task extends Model implements TenantContract
{
    use SoftDeletes;
    use HasFactory;
    use BelongsToTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dt_complete_by' => 'datetime',
        ];
    }

    public function model(): MorphTo
    {
        $instance = $this->morphTo();

        if (method_exists($instance->getModel(), 'user')) {
            return $instance->with('user');
        }

        return $instance;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
        return $query->whereNull('dt_is_completed')->orderByDeadlineDate();
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('dt_is_completed');
    }

    public function scopeWhereAssignedTo($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWhereVisibleTo(Builder $query, User $user)
    {
       if ($user->isAdmin()) {
           return $query->where('is_private', false);
       }

       return $query->where('user_id', $user->id);
    }

    public function scopeCreatedBy(Builder $query, User $user)
    {
        return $query->where('created_by', $user->id);
    }

    public function scopeModelType(Builder $query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    public function scopeAssociatedWithModel(Builder $query, $appointmentId, $reportId): Builder
    {
        return $query->where(function ($query) use ($appointmentId, $reportId) {
            $query->when($appointmentId, function ($query, $appointmentId) {
                $query->where(function ($query) use ($appointmentId) {
                    $query->where('model_type', Appointment::class)
                        ->where('model_id', $appointmentId);
                });
            })->when($reportId, function ($query, $reportId) {
                $query->orWhere(function ($query) use ($reportId) {
                    $query->where('model_type', Report::class)
                        ->where('model_id', $reportId);
                });
            });
        });
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('dt_complete_by', '<=', Carbon::today());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->where('dt_complete_by', Carbon::today());
    }

    public function scopeOrderByDeadlineDate(Builder $query): Builder
    {
        return $query->orderByRaw('IFNULL(dt_complete_by, 1) desc, dt_complete_by ASC');
    }

    protected function isCompleted(): bool
    {
        return is_null($this->dt_is_completed);
    }
}
