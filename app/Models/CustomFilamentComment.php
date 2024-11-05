<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\FilamentComment;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|\Eloquent $subject
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CustomFilamentCommentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFilamentComment withoutTrashed()
 * @mixin \Eloquent
 */
class CustomFilamentComment extends FilamentComment
{
    use HasFactory;
}
