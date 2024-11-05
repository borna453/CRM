<?php

namespace App\Models;

use App\Observers\TenantObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Models\Permission;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\TenantCollection;

/**
 *
 *
 * @property string $id
 * @property string|null $encryption_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array|null $data
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Domain> $domains
 * @property-read int|null $domains_count
 * @property-read mixed $email
 * @property-read mixed $mailer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Domain|null $primaryDomain
 * @property-read \App\Models\User|null $primaryUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static TenantCollection<int, static> all($columns = ['*'])
 * @method static \Database\Factories\TenantFactory factory($count = null, $state = [])
 * @method static TenantCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereEncryptionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereUpdatedAt($value)
 * @mixin \Eloquent
 */

#[ObservedBy(TenantObserver::class)]
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;
    use HasFactory;

    public static function getCustomColumns(): array
    {
        return [
            'id',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function primaryUser(): HasOne
    {
        return $this->hasOne(User::class)->oldestOfMany();
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(Domain::class)->oldestOfMany();
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class, 'tenant_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function route(...$args): string
    {
        $domain = $this->primaryDomain?->domain;

        return tenant_route($domain, ...$args);
    }

    public function getUrl(): string
    {
        return str_replace(env('APP_CENTRAL_DOMAIN'), $this->primaryDomain?->domain, config('app.url'));
    }

    public function getHost(): string
    {
        return str_replace(['http://', 'https://'], '', $this->getUrl());
    }

    public function mailer(): Attribute
    {
        return new Attribute(function () {
            if (isset($this->email['custom_server']) && $this->email['custom_server']) {
                return 'smtp';
            }

            return config('mail.default');
        });
    }

    public function email(): Attribute
    {
        return new Attribute(function (?array $email) {
            if (! isset($email['custom_server'])) {
                $email['host'] = null;
                $email['port'] = null;
                $email['username'] = null;
                $email['password'] = null;
            }

            return $email;
        });
    }
}
