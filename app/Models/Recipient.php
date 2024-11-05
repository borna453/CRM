<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $message_id
 * @property int $user_id
 * @property string|null $seen_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Message $message
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $userReplies
 * @property-read int|null $user_replies_count
 * @method static \Database\Factories\RecipientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereUserId($value)
 * @mixin \Eloquent
 */
class Recipient extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'user_id', 'seen_at'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function replied(): bool
    {
        return Message::where('parent_id', $this->message_id)
            ->where('created_by', $this->user_id)
            ->exists();
    }

    public function userReplies()
    {
        return $this->hasMany(Message::class, 'created_by', 'user_id')
            ->whereColumn('parent_id', 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
