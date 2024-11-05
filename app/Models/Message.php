<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Observers\MessageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

#[ObservedBy(MessageObserver::class)]
/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int|null $parent_id
 * @property int|null $conversation_id
 * @property string|null $recipient_type
 * @property array|null $recipient_ids
 * @property string|null $title
 * @property string $content
 * @property string|null $seen_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Conversation|null $conversation
 * @property-read \App\Models\User|null $creator
 * @property-read int|null $recipients_count
 * @property-read int $view_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipient> $recipients
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Message> $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Database\Factories\MessageFactory factory($count = null, $state = [])
 * @method static Builder|Message forUser()
 * @method static Builder|Message newModelQuery()
 * @method static Builder|Message newQuery()
 * @method static Builder|Message query()
 * @method static Builder|Message whereContent($value)
 * @method static Builder|Message whereConversationId($value)
 * @method static Builder|Message whereCreatedAt($value)
 * @method static Builder|Message whereCreatedBy($value)
 * @method static Builder|Message whereId($value)
 * @method static Builder|Message whereParentId($value)
 * @method static Builder|Message whereRecipientIds($value)
 * @method static Builder|Message whereRecipientType($value)
 * @method static Builder|Message whereSeenAt($value)
 * @method static Builder|Message whereTenantId($value)
 * @method static Builder|Message whereTitle($value)
 * @method static Builder|Message whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Message extends Model implements TenantContract
{
    use HasFactory;
    use BelongsToTenant;

    public const USER = 'user';
    public const COMPANY = 'company';
    public const ALL = 'all';

    protected $fillable = [
        'title',
        'content',
        'recipient_type',
        'recipient_ids',
        'created_by',
        'parent_id',
        'conversation_id'
    ];

    protected $casts = [
        'recipient_ids' => 'array',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function scopeForUser(Builder $query) : Builder
    {
        $userId = auth()->id();
        $companyId = auth()->user()->company_id;

        // check the recipients table for messages that are sent to the user or the company
        $messageIdsForUser = Recipient::where('user_id', $userId)->pluck('message_id');
        $messageIdsForCompany = Recipient::whereIn('user_id', function ($query) use ($companyId) {
            $query->select('id')->from('users')->where('company_id', $companyId);
        })->pluck('message_id');

        $messageIds = $messageIdsForUser->merge($messageIdsForCompany)->unique();

        return $query->where(function ($query) use ($messageIds) {
            $query->whereIn('id', $messageIds)
                ->orWhere('recipient_type', self::ALL);
        });
    }

    public function getViewCountAttribute(): int
    {
        return $this->recipients()->whereNotNull('seen_at')->count();
    }

    public function getRecipientsCountAttribute(): int
    {
        return $this->recipients()->count();
    }
}
