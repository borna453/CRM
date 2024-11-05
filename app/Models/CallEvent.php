<?php

namespace App\Models;

use App\Enums\CallStatus;
use App\Models\Contracts\TenantContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string $call_id
 * @property string|null $tenant_id
 * @property int|null $company_id
 * @property int|null $answered_by
 * @property string|null $to_number
 * @property string|null $from_number
 * @property string $event_time
 * @property string|null $call_type
 * @property CallStatus|null $call_status
 * @property string|null $duration
 * @property string|null $insights_summary
 * @property string|null $sentiment_indicator
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $answeredBy
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Tenant|null $tenant
 * @method static Builder|CallEvent incomingCalls()
 * @method static Builder|CallEvent newModelQuery()
 * @method static Builder|CallEvent newQuery()
 * @method static Builder|CallEvent outgoingCalls()
 * @method static Builder|CallEvent query()
 * @method static Builder|CallEvent whereAnsweredBy($value)
 * @method static Builder|CallEvent whereCallId($value)
 * @method static Builder|CallEvent whereCallStatus($value)
 * @method static Builder|CallEvent whereCallType($value)
 * @method static Builder|CallEvent whereCompanyId($value)
 * @method static Builder|CallEvent whereCreatedAt($value)
 * @method static Builder|CallEvent whereDuration($value)
 * @method static Builder|CallEvent whereEventTime($value)
 * @method static Builder|CallEvent whereFromNumber($value)
 * @method static Builder|CallEvent whereId($value)
 * @method static Builder|CallEvent whereInsightsSummary($value)
 * @method static Builder|CallEvent whereSentimentIndicator($value)
 * @method static Builder|CallEvent whereTenantId($value)
 * @method static Builder|CallEvent whereToNumber($value)
 * @method static Builder|CallEvent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CallEvent extends Model implements TenantContract
{
    use BelongsToTenant;

    protected $guarded = [];

    public const INCOMING_CALL = 'incoming_call';
    public const OUTGOING_CALL = 'outgoing_call';
    public const COMPANY = 'COMPANY';

    protected $casts = [
        'call_status' => CallStatus::class
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function scopeIncomingCalls(Builder $query): Builder
    {
        return $query->where('call_type', self::INCOMING_CALL);
    }

    public function scopeOutgoingCalls(Builder $query): Builder
    {
        return $query->where('call_type', self::OUTGOING_CALL);
    }
}
