<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Observers\CompanyObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int $is_main
 * @property string|null $name
 * @property string|null $address
 * @property string|null $zip_code
 * @property string|null $city
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $coc_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CallEvent> $callEvents
 * @property-read int|null $call_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contract> $contracts
 * @property-read int|null $contracts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Opportunity> $opportunities
 * @property-read int|null $opportunities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PinboardItem> $pinboardItems
 * @property-read int|null $pinboard_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipient> $recipients
 * @property-read int|null $recipients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\CompanyFactory factory($count = null, $state = [])
 * @method static Builder|Company main()
 * @method static Builder|Company newModelQuery()
 * @method static Builder|Company newQuery()
 * @method static Builder|Company onlyTrashed()
 * @method static Builder|Company query()
 * @method static Builder|Company whereAddress($value)
 * @method static Builder|Company whereCity($value)
 * @method static Builder|Company whereCocNumber($value)
 * @method static Builder|Company whereCreatedAt($value)
 * @method static Builder|Company whereDeletedAt($value)
 * @method static Builder|Company whereEmail($value)
 * @method static Builder|Company whereId($value)
 * @method static Builder|Company whereIsMain($value)
 * @method static Builder|Company whereName($value)
 * @method static Builder|Company wherePhoneNumber($value)
 * @method static Builder|Company whereTenantId($value)
 * @method static Builder|Company whereUpdatedAt($value)
 * @method static Builder|Company whereZipCode($value)
 * @method static Builder|Company withMessagesAndViews()
 * @method static Builder|Company withTrashed()
 * @method static Builder|Company withoutAdminCompany()
 * @method static Builder|Company withoutTrashed()
 * @mixin \Eloquent
 */


#[ObservedBy([CompanyObserver::class])]
class Company extends Model implements TenantContract
{
    use HasFactory;
    use SoftDeletes;
    use BelongsToTenant;

    protected $guarded = [];

    public function hasIncompleteDetails(): bool
    {
        $requiredAttributes = ['email', 'address', 'zip_code', 'city'];

        foreach ($requiredAttributes as $attribute) {
            if (is_null($this->$attribute)) {
                return true;
            }
        }

        return false;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function callEvents()
    {
        return $this->hasMany(CallEvent::class);
    }

    public function pinboardItems(): HasManyThrough
    {
        return $this->hasManyThrough(PinboardItem::class, User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->where('recipient_type', Message::COMPANY)
            ->whereJsonContains('recipient_ids', $this->id);
    }

    public function recipients(): HasManyThrough
    {
        return $this->hasManyThrough(Recipient::class, User::class, 'company_id', 'user_id');
    }

    public function scopeWithMessagesAndViews(Builder $query): Builder
    {
        return $query->withCount([
            'users as recipients_count',
            'users as views_count' => function ($query) {
                $query->whereHas('recipients', function ($query) {
                    $query->whereNotNull('seen_at');
                });
            },
        ]);
    }

    public function scopeWithoutAdminCompany(Builder $query): Builder
    {
        return $query->where('id', '!=', auth()->user()->company_id);
    }

    public function scopeMain(Builder $query): Builder
    {
        return $query->where('is_main', true);
    }
}
