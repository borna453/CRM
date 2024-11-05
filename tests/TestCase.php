<?php

namespace Tests;

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Utils\PermissionSeederHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use WithoutModelEvents;
    public Company $company;
    public User $adminUser;
    public User $regularUser;
    public User $ownerUser;
    public User $employeeUser;

    public Role $employeeRole;
    public Role $userRole;


    // Assume all tests are for tenant features.
    protected bool $tenancy = true;

    public Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->artisan('db:seed --class=NotificationTemplateSeeder');

        if ($this->tenancy) {
            $this->initializeTenancy();
        }

        Role::create([
            'name' => User::OWNER,
            'guard_name' => 'web',
        ]);
        Role::create([
            'name' => User::SUPERADMIN,
            'guard_name' => 'web',
        ]);

        $this->userRole = Role::create([
            'name' => User::USER,
            'guard_name' => 'web',
        ]);
        $adminRole = Role::create([
            'name' => User::ADMIN,
            'guard_name' => 'web',
        ]);

        $this->employeeRole = Role::create([
           'name' => User::EMPLOYEE,
            'guard_name' => 'web',
        ]);

        PermissionSeederHelper::assignPermissionsByRole($adminRole, $this->tenant->id, User::ADMIN);
        PermissionSeederHelper::assignPermissionsByRole($this->userRole, $this->tenant->id, User::USER);
        PermissionSeederHelper::assignPermissionsByRole($this->employeeRole, $this->tenant->id, User::EMPLOYEE);

        $this->company = Company::create([
            'name' => 'Cloudmazing',
            'email' => 'cloudmazing@company.nl',
            'phone_number' => '1234567890',
            'address' => 'Cloudmazing street 1',
            'zip_code' => '1234AB',
            'city' => 'Amsterdam',
            'coc_number' => '12345678',
            'is_main' => 1,
            'id' => 1,
        ]);

        $this->adminUser = User::factory()->create([
            'first_name' => 'Admin',
            'email' => 'admin@cloudmazing.nl',
            'id' => 1,
            'company_id' => $this->company->id,
            'timezone' => 'Europe/Amsterdam',
            'email_enabled' => true,
        ])->assignRole(User::ADMIN);

        $this->regularUser = User::factory()->create([
            'first_name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@cloudmazing.nl',
            'id' => 2,
            'login_allowed' => true,
            'company_id' => $this->company->id,
            'email_enabled' => true,
            'timezone' => 'Europe/Amsterdam',
        ])->assignRole(User::USER);

        $this->ownerUser = User::factory()->create([
            'first_name' => 'Owner',
            'email' => 'owner@cloudmazing.nl',
            'id' => 3,
            'login_allowed' => true,
        ])->assignRole(User::OWNER);

        $this->employeeUser = User::factory()->create([
            'first_name' => 'Employee',
            'email' => 'employee@cloudmazing.nl',
            'id' => 4,
            'login_allowed' => true,
            'company_id' => $this->company->id,
            'email_enabled' => true,
            'timezone' => 'Europe/Amsterdam',
        ])->assignRole(User::EMPLOYEE);

        \App\Models\Feature::create([
            'name' => 'appointments_and_reports',
            'scope' => 'global',
            'value' => 1,
            'tenant_id' => $this->tenant->id,
        ]);

        // Default to acting as the admin user
        $this->actingAs($this->adminUser);
    }

    public function initializeTenancy(): void
    {
        Model::withoutEvents(function (){
            $this->tenant = Tenant::create([
                'id' => 'cloudmazing',
                'rinkel' => '3fb596d0-46c9-4aaf-8e66-e405cfca3d24'
            ]);

            $this->tenant->domains()->create([
                'domain' => env('APP_TEST_TENANT_DOMAIN'),
            ]);
        });

        tenancy()->initialize($this->tenant);
    }
}
