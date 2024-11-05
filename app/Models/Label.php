<?php

namespace App\Models;

use App\Enums\LabelTypes;
use App\Models\Contracts\TenantContract;
use App\Models\Scopes\LabelSortingScope;
use App\Observers\LabelObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;


/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property string $name
 * @property string $color
 * @property string $type
 * @property int $finished_state
 * @property int|null $order_column
 * @property int $show_on_board
 * @property int|null $should_archive
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Opportunity> $opportunities
 * @property-read int|null $opportunities_count
 * @property-read \App\Models\Tenant|null $tenant
 * @method static Builder|Label contractType()
 * @method static Builder|Label dealType()
 * @method static Builder|Label newModelQuery()
 * @method static Builder|Label newQuery()
 * @method static Builder|Label opportunityType()
 * @method static Builder|Label query()
 * @method static Builder|Label showOnBoard()
 * @method static Builder|Label whereColor($value)
 * @method static Builder|Label whereCreatedAt($value)
 * @method static Builder|Label whereFinishedState($value)
 * @method static Builder|Label whereId($value)
 * @method static Builder|Label whereName($value)
 * @method static Builder|Label whereOrderColumn($value)
 * @method static Builder|Label whereShouldArchive($value)
 * @method static Builder|Label whereShowOnBoard($value)
 * @method static Builder|Label whereTenantId($value)
 * @method static Builder|Label whereType($value)
 * @method static Builder|Label whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[ObservedBy(LabelObserver::class)]
class Label extends Model implements TenantContract
{
    use BelongsToTenant;

    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new LabelSortingScope());
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'label_id');
    }

    public function scopeShowOnBoard(Builder $query): Builder
    {
        return $query->where('show_on_board', true)->where('type', '=', 'Opportunity');
    }

    public function scopeOpportunityType(Builder $query): Builder
    {
        return $query->where('type', LabelTypes::Opportunity);
    }

    public function scopeContractType(Builder $query): Builder
    {
        return $query->where('type', LabelTypes::Contract);
    }

    public function scopeDealType(Builder $query): Builder
    {
        return $query->where('type', LabelTypes::Deals);
    }
}
