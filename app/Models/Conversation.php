<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property int $id
 * @property int $created_by
 * @property int|null $parent_message_id
 * @property bool|null $dt_is_completed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read int $admin_unread_replies_count
 * @property-read string|null $last_reply_time
 * @property-read int $replies_count
 * @property-read int $user_unread_replies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\Message|null $parentMessage
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereDtIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereParentMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Conversation extends Model
{
    protected $fillable = ['created_by', 'parent_message_id', 'dt_is_completed'];

    protected $casts = [
        'dt_is_completed' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function getLastReplyTimeAttribute(): ?string
    {
        $lastMessage = $this->messages()->latest()->first();

        return $lastMessage ? $lastMessage->created_at : null;
    }

    public function getRepliesCountAttribute(): int
    {
        return $this->messages()->count();
    }

    public function getUserUnreadRepliesCountAttribute(): int
    {
        return $this->messages()
            ->whereNotNull('parent_id')
            ->whereNull('seen_at')
            ->whereHas('creator', function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('name', User::USER);
                });
            })
            ->count();
    }

    public function getAdminUnreadRepliesCountAttribute(): int
    {
        return $this->messages()
            ->whereNotNull('parent_id')
            ->whereNull('seen_at')
            ->whereHas('creator', function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('name', User::ADMIN);
                });
            })
            ->count();
    }
}
