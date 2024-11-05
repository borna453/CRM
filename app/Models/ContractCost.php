<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int $contract_id
 * @property string $description
 * @property float $cost_estimate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Contract $contract
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost whereCostEstimate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractCost whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ContractCost extends Model implements TenantContract
{
    use BelongsToTenant;

    protected $guarded = [];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
