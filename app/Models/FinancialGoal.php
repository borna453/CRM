<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property string $goal
 * @property string|null $achieved
 * @property string $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal query()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal whereAchieved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal whereGoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialGoal whereYear($value)
 * @mixin \Eloquent
 */
class FinancialGoal extends Model implements TenantContract
{
    use BelongsToTenant;

    protected $guarded = [];
}
