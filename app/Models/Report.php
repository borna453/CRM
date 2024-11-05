<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Notifications\ReportPublished;
use App\Observers\ReportObserver;
use App\Traits\ScopeWhereVisibleToTrait;
use Carbon\Carbon;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Livewire\WithFileUploads;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 *
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Appointment|null $appointment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Parallax\FilamentComments\Models\FilamentComment> $filamentComments
 * @property-read int|null $filament_comments_count
 * @property-read mixed $is_published
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ReportFactory factory($count = null, $state = [])
 * @method static Builder|Report newModelQuery()
 * @method static Builder|Report newQuery()
 * @method static Builder|Report onlyTrashed()
 * @method static Builder|Report published()
 * @method static Builder|Report query()
 * @method static Builder|Report toPublish()
 * @method static Builder|Report whereCreatedAt($value)
 * @method static Builder|Report whereDate($value)
 * @method static Builder|Report whereDeletedAt($value)
 * @method static Builder|Report whereDescription($value)
 * @method static Builder|Report whereId($value)
 * @method static Builder|Report wherePast()
 * @method static Builder|Report wherePublishedAt($value)
 * @method static Builder|Report whereTenantId($value)
 * @method static Builder|Report whereTitle($value)
 * @method static Builder|Report whereUpdatedAt($value)
 * @method static Builder|Report whereUserId($value)
 * @method static Builder|Report whereVisibleTo(\App\Models\User $user)
 * @method static Builder|Report withTrashed()
 * @method static Builder|Report withoutTrashed()
 * @mixin \Eloquent
 */

#[ObservedBy([ReportObserver::class])]
class Report extends Model implements HasMedia, TenantContract
{
    use HasFactory;
    use InteractsWithMedia;
    use WithFileUploads;
    use HasFilamentComments;
    use ScopeWhereVisibleToTrait;
    use SoftDeletes;
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'model');
    }

    public function publish()
    {
        $this->update(['published_at' => Carbon::now()]);

        $this->user->notify(new ReportPublished($this));

        event(new DatabaseNotificationsSent($this->user));
    }

    public function scopeWherePast(Builder $query): Builder
    {
        return $query->whereHas('appointment', function ($query) {
                $query->where('dt_start', '<=', Carbon::now());
            });
    }
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at');
    }

    public function scopeToPublish(Builder $query): void
    {
        $query->whereNull('published_at');
    }

    public function isPublished():Attribute
    {
        return Attribute::make(
            get: fn () => !empty($this->published_at)
        );
    }

    public function hasDocuments(): bool
    {
        return $this->hasMedia('document-attachments');
    }

    public function hasImages(): bool
    {
        return $this->hasMedia('image-attachments');
    }
}
