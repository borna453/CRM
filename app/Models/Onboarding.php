<?php

namespace App\Models;

use App\Enums\OnboardingTypes;
use App\Models\Contracts\TenantContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 *
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property string $step
 * @property int $is_complete
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant|null $tenant
 * @method static Builder|Onboarding newModelQuery()
 * @method static Builder|Onboarding newQuery()
 * @method static Builder|Onboarding query()
 * @method static Builder|Onboarding tenantId()
 * @method static Builder|Onboarding whereCreatedAt($value)
 * @method static Builder|Onboarding whereId($value)
 * @method static Builder|Onboarding whereIsComplete($value)
 * @method static Builder|Onboarding whereStep($value)
 * @method static Builder|Onboarding whereTenantId($value)
 * @method static Builder|Onboarding whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Onboarding extends Model implements TenantContract
{
    use BelongsToTenant;

    protected $guarded = [];

    public function scopeTenantId(Builder $query): Builder
    {
        return $query->where('tenant_id', tenant()->id);
    }

    public static function markStepAsComplete(OnboardingTypes $step): void
    {
          self::create([
              'tenant_id' => tenant()->id,
              'step' => $step->value,
              'is_complete' => true,
          ]);
    }
}
