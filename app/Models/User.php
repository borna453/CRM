<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use App\Observers\UserObserver;
use App\Traits\SearchScopeTrait;
use Archilex\AdvancedTables\Concerns\HasViews;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use function Laravel\Prompts\search;

/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property int|null $company_id
 * @property string|null $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property bool|null $email_enabled
 * @property int $should_invite
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property bool|null $login_allowed
 * @property int|null $last_activity
 * @property mixed|null $password
 * @property string|null $remember_token
 * @property string $locale
 * @property string|null $timezone
 * @property string|null $invited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Collection<int, \App\Models\Appointment> $appointments
 * @property-read int|null $appointments_count
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $is_login_allowed
 * @property-read Collection<int, \Archilex\AdvancedTables\Models\ManagedPresetView> $managedPresetViews
 * @property-read int|null $managed_preset_views_count
 * @property-read Collection<int, \Archilex\AdvancedTables\Models\UserView> $managedUserViews
 * @property-read int|null $managed_user_views_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, \App\Models\PinboardItem> $pinboardItems
 * @property-read int|null $pinboard_items_count
 * @property-read Collection<int, \App\Models\Recipient> $recipients
 * @property-read int|null $recipients_count
 * @property-read Collection<int, \App\Models\Report> $reports
 * @property-read int|null $reports_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\Tenant|null $tenant
 * @method static Builder|User assignableUsers()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User isAssignableUser()
 * @method static Builder|User isNotSuperAdminOrOwner()
 * @method static Builder|User last20ActiveUsers()
 * @method static Builder|User mainCompanyUsers()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions, $without = false)
 * @method static Builder|User query()
 * @method static Builder|User regularUser()
 * @method static Builder|User role($roles, $guard = null, $without = false)
 * @method static Builder|User search(string $searchTerm, string $column, ?string $additionalColumn = null)
 * @method static Builder|User visibleTo()
 * @method static Builder|User whereCompanyId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailEnabled($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereInvitedAt($value)
 * @method static Builder|User whereLastActivity($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereLocale($value)
 * @method static Builder|User whereLoginAllowed($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereShouldInvite($value)
 * @method static Builder|User whereTenantId($value)
 * @method static Builder|User whereTimezone($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutPermission($permissions)
 * @method static Builder|User withoutRole($roles, $guard = null)
 * @method static Builder|User withoutTrashed()
 * @mixin \Eloquent
 */


#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements FilamentUser, TenantContract
{
    use HasFactory;
    use Notifiable;
    use SearchScopeTrait;
    use HasRoles;
    use HasViews;
    use SoftDeletes;
    use BelongsToTenant;
    use CanResetPassword;

    const OWNER = 'owner';
    const SUPERADMIN = 'superadmin';
    const USER = 'user';
    const ADMIN = 'admin';
    const EMPLOYEE = 'employee';

    const COMPANY_USERS = 'company_users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('eager_loading_relations', function (Builder $builder) {
            $builder->with(['roles', 'permissions']);
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'login_allowed' => 'boolean',
            'email_enabled' => 'boolean',
        ];
    }

    public function timezone(): Attribute
    {
        return new Attribute(fn (?string $value) => $value ?? 'Europe/Amsterdam');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === self::USER) {
            return ($this->isUser() && $this->login_allowed);
        }

        if ($panel->getId() === self::SUPERADMIN) {
            return $this->isSuperAdmin();
        }

        if ($panel->getId() === self::OWNER) {
            return $this->isOwner();
        }

        return $panel->getId() === self::ADMIN && $this->isAdmin() || $this->isEmployee();
    }

    public function canAccessNotificationPreview(): void
    {
        if(!$this->isAdmin() && !$this->isOwner()){
            abort(403);
        }
    }
    public function canBeImpersonated()
    {
        if ($this->isOwner()) {
            return false;
        }

        if (auth()->user()->isSuperAdmin() || auth()->user()->isOwner()) {
            return true;
        }
        if (auth()->user()->isAdmin()) {
            return $this->isUser() || $this->isEmployee();
        }

        if(auth()->user()->isEmployee()){
            return $this->isUser();
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ADMIN);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::SUPERADMIN);
    }

    public function isOwner(): bool
    {
        return $this->hasRole(self::OWNER);
    }

    public function isUser(): bool
    {
        return $this->hasRole(self::USER);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(self::EMPLOYEE);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function pinboardItems(): HasMany
    {
        return $this->hasMany(PinboardItem::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    public function scopeVisibleTo(Builder $query): Builder
    {
        if (auth()->user()->isSuperAdmin()) {
            return $query;
        }

        return $query->IsNotSuperAdminOrOwner();
    }

    public function scopeIsNotSuperAdminOrOwner(Builder $query): Builder
    {
        return $query->withoutRole(self::SUPERADMIN)->withoutRole(self::OWNER);
    }

    public function scopeAssignableUsers(Builder $query): Builder
    {
        return $query->withoutRole(self::SUPERADMIN)->where('login_allowed', true);
    }

    public function scopeRegularUser(Builder $query): Builder
    {
        return $query->withoutRole(self::SUPERADMIN)->withoutRole(self::ADMIN);
    }

    public function scopeIsAssignableUser(Builder $query): Builder
    {
        return $query->role(self::USER);
    }

    public function scopeLast20ActiveUsers(Builder $query): Builder
    {
        return $query->whereNotNull('last_activity')
            ->orderBy('last_activity', 'desc')
            ->limit(20);
    }

    public function scopeMainCompanyUsers(Builder $query): Builder
    {
        return $query->where('company_id', Company::main()->first()->id);
    }

    protected function getIsLoginAllowedAttribute()
    {
        //return null if its admin user
        if ($this->isAdmin()) {
            return null;
        }
        return $this->login_allowed;
    }

    public function hasOpenPinboardItems(): bool
    {
        return $this->pinboardItems()->open()->count() > 0;
    }

    public function hasPastAppointments(): bool
    {
        return $this->appointments()->wherePast()->count() > 0;
    }

    public function hasAppointments(): bool
    {
        return $this->appointments()->count() > 0;
    }

    public function invite()
    {
        $this->update([
            'invited_at' => Carbon::now(),
        ]);
    }

    public function name(): Attribute
    {
        return new Attribute(function (?string $state) {
            return $state ?? $this->first_name . ' ' . $this->last_name;
        });
    }

    public static function splitName(string $name): array
    {
        $first_name = str($name)->before(' ')->toString();
        $last_name = str_contains($name, ' ') ? str($name)->after(' ')->toString() : null;

        return [$first_name, $last_name];
    }

    public function getTenantPermissions(): Collection
    {
        return Permission::where('tenant_id', $this->tenant_id)
            ->whereIn('id', function ($query) {
                $query->select('permission_id')
                    ->from('role_has_permissions')
                    ->whereIn('role_id', $this->roles->pluck('id'))
                    ->where('tenant_id', $this->tenant_id);
            })
            ->get();
    }

    public function hasTenantPermissionTo(string $permissionName): bool
    {
        return $this->getTenantPermissions()->contains('name', $permissionName);
    }
}
