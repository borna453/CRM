<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Observers\OpportunityObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\EloquentSortable\SortableTrait;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int|null $user_id
 * @property int|null $company_id
 * @property int|null $label_id
 * @property string|null $title
 * @property string|null $expected_revenue
 * @property string|null $cost_estimate
 * @property string|null $text
 * @property string|null $closed_at
 * @property int|null $order_column
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $formatted_cost_estimate
 * @property-read mixed $formatted_revenue
 * @property-read \App\Models\Label|null $label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\Tenant|null $tenant
 * @method static Builder|Opportunity closed()
 * @method static \Database\Factories\OpportunityFactory factory($count = null, $state = [])
 * @method static Builder|Opportunity newModelQuery()
 * @method static Builder|Opportunity newQuery()
 * @method static Builder|Opportunity open()
 * @method static Builder|Opportunity ordered(string $direction = 'asc')
 * @method static Builder|Opportunity query()
 * @method static Builder|Opportunity whereClosedAt($value)
 * @method static Builder|Opportunity whereCompanyId($value)
 * @method static Builder|Opportunity whereCostEstimate($value)
 * @method static Builder|Opportunity whereCreatedAt($value)
 * @method static Builder|Opportunity whereExpectedRevenue($value)
 * @method static Builder|Opportunity whereId($value)
 * @method static Builder|Opportunity whereLabelId($value)
 * @method static Builder|Opportunity whereOrderColumn($value)
 * @method static Builder|Opportunity whereTenantId($value)
 * @method static Builder|Opportunity whereText($value)
 * @method static Builder|Opportunity whereTitle($value)
 * @method static Builder|Opportunity whereUpdatedAt($value)
 * @method static Builder|Opportunity whereUserId($value)
 * @mixin \Eloquent
 */

#[ObservedBy([OpportunityObserver::class])]
class Opportunity extends Model implements TenantContract
{
    use HasFactory;
    use BelongsToTenant;
    use SortableTrait;

    const string COMPANY_OPPORTUNITIES = 'company_opportunities';

    protected $guarded = [];

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'model');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'model');
    }

    public function close(): void
    {
        $this->update([
            'closed_at' => now(),
        ]);
    }

    public function scopeClosed(Builder $query): void
    {
        $query->whereNotNull('closed_at');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('closed_at');
    }

    protected function isClosed(): bool
    {
        return is_null($this->closed_at);
    }

    public function getFormattedRevenueAttribute()
    {
        return '€' . number_format($this->expected_revenue, 2, ',', '.');
    }

    public function getFormattedCostEstimateAttribute()
    {
        return '€' . number_format($this->cost_estimate, 2, ',', '.');
    }
}
