<?php

namespace App\Filament\Owner\Resources\TenantResource\Pages;

use App\Enums\OnboardingTypes;
use App\Enums\Permissions;
use App\Filament\Owner\Resources\TenantResource;
use App\Models\Company;
use App\Models\Feature;
use App\Models\Onboarding;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantWelcomeEmailNotification;
use App\Utils\PermissionSeederHelper;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Tenant $tenant */
        $tenant = parent::handleRecordCreation($data);

        $tenant->rinkel = uuid_create();
        $tenant->save();

        $company = Company::create([
            'name' => $data['company']['name'],
            'tenant_id' => $data['id'],
            'is_main' => true,
        ]);

        $randomPassword = Str::random(16);

        $user = User::withoutEvents(function () use ($data, $company, $randomPassword) {
            $name = strstr($data['users']['email'], '@', true);
            [$first_name, $last_name] = User::splitName($name);

            return User::create([
                'first_name' => ucwords($first_name),
                'last_name' => ucwords($last_name),
                'email' => $data['users']['email'],
                'password' => bcrypt($randomPassword),
                'tenant_id' => $data['id'],
                'login_allowed' => true,
                'email_enabled' => true,
                'company_id' => $company->id,
            ])->assignRole(User::ADMIN);
        });

        Domain::create([
            'domain' => $tenant->id . '.' . config('custom.central_domain'),
            'tenant_id' => $tenant->id,
        ]);

        foreach (OnboardingTypes::getOrderedSteps() as $step) {
            Onboarding::create([
                'tenant_id' => $tenant->id,
                'step' => $step->value,
                'is_complete' => false,
            ]);
        }

        $user->notify(new TenantWelcomeEmailNotification($tenant, $user));

        $user->update([
            'invited_at' => Carbon::now()
        ]);

        $adminRole = Role::where('name', User::ADMIN)->first();
        $userRole = Role::where('name', User::USER)->first();
        $employeeRole = Role::where('name', User::EMPLOYEE)->first();


        PermissionSeederHelper::assignPermissionsByRole($adminRole, $tenant->id, User::ADMIN);
        PermissionSeederHelper::assignPermissionsByRole($userRole, $tenant->id, User::USER);
        PermissionSeederHelper::assignPermissionsByRole($employeeRole, $tenant->id, User::EMPLOYEE);

        Feature::create(['name' => 'appointments_and_reports', 'scope' => 'global', 'value' => 1, 'tenant_id' => $tenant->id]);
        Feature::create(['name' => 'administration', 'scope' => 'global', 'value' => 1, 'tenant_id' => $tenant->id]);
        Feature::create(['name' => 'rinkel', 'scope' => 'global', 'value' => 0, 'tenant_id' => $tenant->id]);


        return $tenant;
    }
}
