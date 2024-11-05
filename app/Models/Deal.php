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
 * @property int $company_id
 * @property float $total_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|Deal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereTotalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Deal extends Model implements TenantContract
{
    use BelongsToTenant;

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
