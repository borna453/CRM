<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Models\Scopes\AppointmentUserScope;
use App\Observers\AppointmentObserver;
use App\Traits\ScopeWhereVisibleToTrait;
use App\Traits\SearchScopeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int $user_id
 * @property int|null $report_id
 * @property int|null $created_by
 * @property string $title
 * @property string|null $location
 * @property string|null $online_url
 * @property string|null $other_location
 * @property string|null $description
 * @property string|null $internal_notes
 * @property \Illuminate\Support\Carbon|null $invoiced_at
 * @property \Illuminate\Support\Carbon $dt_start
 * @property \Illuminate\Support\Carbon $dt_end
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Report|null $report
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \App\Models\User $user
 * @method static Builder|Appointment belongsToCompany($companyId)
 * @method static \Database\Factories\AppointmentFactory factory($count = null, $state = [])
 * @method static Builder|Appointment newModelQuery()
 * @method static Builder|Appointment newQuery()
 * @method static Builder|Appointment onlyTrashed()
 * @method static Builder|Appointment query()
 * @method static Builder|Appointment search(string $searchTerm, string $column, ?string $additionalColumn = null)
 * @method static Builder|Appointment shouldBeInvoiced()
 * @method static Builder|Appointment whereCreatedAt($value)
 * @method static Builder|Appointment whereCreatedBy($value)
 * @method static Builder|Appointment whereDeletedAt($value)
 * @method static Builder|Appointment whereDescription($value)
 * @method static Builder|Appointment whereDtEnd($value)
 * @method static Builder|Appointment whereDtStart($value)
 * @method static Builder|Appointment whereId($value)
 * @method static Builder|Appointment whereInternalNotes($value)
 * @method static Builder|Appointment whereInvoicedAt($value)
 * @method static Builder|Appointment whereLocation($value)
 * @method static Builder|Appointment whereOnlineUrl($value)
 * @method static Builder|Appointment whereOtherLocation($value)
 * @method static Builder|Appointment wherePast()
 * @method static Builder|Appointment whereReportId($value)
 * @method static Builder|Appointment whereTenantId($value)
 * @method static Builder|Appointment whereTitle($value)
 * @method static Builder|Appointment whereUpcoming()
 * @method static Builder|Appointment whereUpdatedAt($value)
 * @method static Builder|Appointment whereUserId($value)
 * @method static Builder|Appointment whereVisibleTo(\App\Models\User $user)
 * @method static Builder|Appointment withTrashed()
 * @method static Builder|Appointment withoutTrashed()
 * @mixin \Eloquent
 */

#[ObservedBy([AppointmentObserver::class])]
class Appointment extends Model implements HasMedia, TenantContract
{
    use InteractsWithMedia;
    use HasFactory;
    use ScopeWhereVisibleToTrait;
    use SearchScopeTrait;
    use SoftDeletes;
    use BelongsToTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dt_start' => 'datetime',
            'dt_end' => 'datetime',
            'invoiced_at' => 'datetime',
        ];
    }

    protected function dtStart(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->setTimezone(auth()->user()?->timezone ?? 'UTC')
        );
    }

    protected function dtEnd(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->setTimezone(auth()->user()?->timezone ?? 'UTC')
        );
    }

    protected static function booted()
    {
        static::addGlobalScope(new AppointmentUserScope());
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'model');
    }

    public function report(): HasOne
    {
        return $this->hasOne(Report::class, 'id', 'report_id');
    }

    public function scopeWhereUpcoming(Builder $query): Builder
    {
        return $query->where('dt_end', '>=', now());
    }

    public function scopeWherePast(Builder $query): Builder
    {
        return $query->where('dt_end', '<', Carbon::now());
    }

    public function scopeShouldBeInvoiced(Builder $query): Builder
    {
        return $query->whereNull('invoiced_at');
    }

    public function scopeBelongsToCompany(Builder $query, $companyId)
    {
        return $query->whereHas('user', function (Builder $query) use ($companyId) {
            $query->where('company_id', $companyId);
        });
    }

    public static function hasPublishedReports(): bool
    {
        return self::whereHas('report', function (Builder $query) {
            $query->whereNotNull('published_at');
        })->exists();
    }

    public function invoice()
    {
        $this->update([
            'invoiced_at' => now(),
        ]);
    }
}
