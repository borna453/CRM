<?php

namespace App\Models;

use App\Enums\NotificationTypeEnum;
use App\Models\Contracts\TenantContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property string $type
 * @property string|null $email_subject
 * @property string|null $email_content
 * @property string|null $database_subject
 * @property string|null $database_content
 * @property string|null $button_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant|null $tenant
 * @method static Builder|NotificationTemplate adminNotifications()
 * @method static Builder|NotificationTemplate newModelQuery()
 * @method static Builder|NotificationTemplate newQuery()
 * @method static Builder|NotificationTemplate query()
 * @method static Builder|NotificationTemplate whereButtonText($value)
 * @method static Builder|NotificationTemplate whereCreatedAt($value)
 * @method static Builder|NotificationTemplate whereDatabaseContent($value)
 * @method static Builder|NotificationTemplate whereDatabaseSubject($value)
 * @method static Builder|NotificationTemplate whereEmailContent($value)
 * @method static Builder|NotificationTemplate whereEmailSubject($value)
 * @method static Builder|NotificationTemplate whereId($value)
 * @method static Builder|NotificationTemplate whereTenantId($value)
 * @method static Builder|NotificationTemplate whereType($value)
 * @method static Builder|NotificationTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NotificationTemplate extends Model implements TenantContract
{
    use BelongsToTenant;

    protected $guarded = [];

    public function scopeAdminNotifications(Builder $query): Builder
    {
        return $query->where('type', '!=' ,NotificationTypeEnum::TENANT_WELCOME_EMAIL);
    }

    public function getRouteKey()
    {
        return $this->type;
    }
}
