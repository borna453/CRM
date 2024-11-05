<?php

namespace Database\Seeders;

use App\Enums\LabelTypes;
use App\Enums\PrimaryColor;
use App\Models\Appointment;
use App\Models\Company;
use App\Models\Feature;
use App\Models\FinancialGoal;
use App\Models\Label;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use App\Utils\PermissionSeederHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $adminRole = null;
        $userRole = null;
        $employeeRole = null;

        Model::withoutEvents(function() use (&$adminRole, &$userRole, &$employeeRole) {
            Role::updateOrCreate(['name' => User::OWNER, 'guard_name' => 'web']);
            Role::updateOrCreate(['name' => User::SUPERADMIN, 'guard_name' => 'web']);
            $userRole = Role::updateOrCreate(['name' => User::USER, 'guard_name' => 'web']);
            $adminRole = Role::updateOrCreate(['name' => User::ADMIN, 'guard_name' => 'web']);
            $employeeRole = Role::updateOrCreate(['name' => User::EMPLOYEE, 'guard_name' => 'web']);

            User::updateOrCreate(['email' => 'job@cloudmazing.nl'], [
                'first_name' => 'Job',
                'last_name' => 'Wiegant',
                'password' => bcrypt('secret'),
                'login_allowed' => true,
            ])->assignRole(User::OWNER);
        });

        if(!app()->isProduction()){
            // withoutEvents requires manually filling tenant_id columns.
            Model::withoutEvents(function () use($faker, $adminRole, $userRole, $employeeRole) {
                /** @var Tenant $tenant */
                $tenant = Tenant::query()->create([
                    'id' => 'cloudmazing',
                ]);
                $tenant->domains()->create(['domain' => env('APP_TEST_TENANT_DOMAIN', 'cloudmazing.'.env('APP_CENTRAL_DOMAIN', 'klantenconnect.lan'))]);

                /** @var Tenant $tenant */
                $test = Tenant::query()->create([
                    'id' => 'test',
                ]);
                $test->domains()->create(['domain' => env('APP_TEST_2_TENANT_DOMAIN', 'test.'.env('APP_CENTRAL_DOMAIN', 'klantenconnect.lan'))]);

                foreach ([$tenant, $test] as $tenant) {
                    $tenant->run(function () use ($tenant, $faker, $adminRole, $userRole, $employeeRole) {
                        $namePrefix = $tenant->id === 'test' ? 'test ' : '';
                        $emailPrefix = $tenant->id === 'test' ? 'test.' : '';

                        FinancialGoal::create([
                           'tenant_id' => $tenant->id,
                            'year' => date('Y'),
                            'goal' => 100000,
                            'achieved' => 0,
                        ]);

                        $colors = PrimaryColor::cases();
                        shuffle($colors);

                        for ($i = 1; $i < 7; $i++) {
                            $randomColor = $colors[($i - 1) % count($colors)]->value;

                            Label::create([
                                'tenant_id' => $tenant->id,
                                'name' => $namePrefix . 'Test Label ' . $i,
                                'color' => $randomColor,
                                'type' => LabelTypes::Opportunity,
                                'order_column' => $i,
                                'should_archive' => $i % 6 == 0,
                                'finished_state' => $i % 5 == 0,
                                'show_on_board' => $i < 6,
                            ]);
                        }

                        $mainCompany = Company::updateOrCreate([
                            'tenant_id' => $tenant->id,
                            'name' => $namePrefix.'Cloudmazing Software',
                            'address' => 'Grotestraat 26-1',
                            'zip_code' => '7471 BP',
                            'city' => 'Goor',
                            'email' => $emailPrefix.'info@cloudmazing.nl',
                            'phone_number' => '+31547234500',
                            'coc_number' => '12345678',
                            'is_main' => true,
                        ]);

                        $companies = [$mainCompany];
                        for ($i = 0; $i < 5; $i++) {
                            $company = Company::updateOrCreate([
                                'tenant_id' => $tenant->id,
                                'name' => $namePrefix.$faker->company,
                                'address' => $faker->address,
                                'zip_code' => $faker->postcode,
                                'city' => $faker->city,
                                'email' => $emailPrefix.$faker->companyEmail,
                                'phone_number' => $faker->phoneNumber,
                                'coc_number' => $faker->randomNumber(8),
                            ]);
                            $companies[] = $company;
                        }

                        User::updateOrCreate(['email' => $emailPrefix.'superadmin@cloudmazing.nl'], [
                            'tenant_id' => $tenant->id,
                            'first_name' => $namePrefix.'Super Admin',
                            'password' => bcrypt('secret'),
                            'login_allowed' => true,
                        ])->assignRole(User::SUPERADMIN);

                        $admin = User::updateOrCreate(['email' => $emailPrefix.'admin@cloudmazing.nl'], [
                            'tenant_id' => $tenant->id,
                            'first_name' => $namePrefix.'Admin User',
                            'password' => bcrypt('secret'),
                            'login_allowed' => true,
                            'company_id' => $mainCompany->id,
                        ])->assignRole(User::ADMIN);

                        $user = User::updateOrCreate(['email' => $emailPrefix.'user@cloudmazing.nl'], [
                            'tenant_id' => $tenant->id,
                            'first_name' => $namePrefix.User::USER,
                            'password' => bcrypt('secret'),
                            'email_enabled' => false,
                            'company_id' => $mainCompany->id,
                            'login_allowed' => true,
                        ])->assignRole(User::USER);

                        $employee = User::updateOrCreate(['email' => $emailPrefix.'employee@cloudmazing.nl'],[
                            'tenant_id' => $tenant->id,
                            'first_name' => $namePrefix.'Employee User',
                            'password' => bcrypt('secret'),
                            'email_enabled' => true,
                            'login_allowed' => true,
                            'company_id' => $mainCompany->id,
                        ])->assignRole(User::EMPLOYEE);


                        PermissionSeederHelper::assignPermissionsByRole($adminRole, $tenant->id, User::ADMIN);
                        PermissionSeederHelper::assignPermissionsByRole($userRole, $tenant->id, User::USER);
                        PermissionSeederHelper::assignPermissionsByRole($employeeRole, $tenant->id, User::EMPLOYEE);


                        Feature::create(['name' => 'appointments_and_reports', 'scope' => 'global', 'value' => 1, 'tenant_id' => $tenant->id]);
                        Feature::create(['name' => 'administration', 'scope' => 'global', 'value' => 1, 'tenant_id' => $tenant->id]);
                        Feature::create(['name' => 'rinkel', 'scope' => 'global', 'value' => 1, 'tenant_id' => $tenant->id]);

                        // Ensure at least one user per company
                        foreach ($companies as $company) {
                            User::updateOrCreate(['email' => $emailPrefix.$faker->unique()->email], [
                                'tenant_id' => $tenant->id,
                                'first_name' => $namePrefix.$faker->firstName,
                                'last_name' => $faker->lastName,
                                'password' => bcrypt('secret'),
                                'login_allowed' => true,
                                'email_enabled' => true,
                                'company_id' => $company->id,
                            ])->assignRole(User::USER);
                        }

                        // Create additional random users
                        for ($i = 0; $i < 10; $i++) {
                            $company = $companies[array_rand($companies)];
                            $role = $i % 2 == 0 ? User::ADMIN : User::USER;
                            User::updateOrCreate(['email' => $emailPrefix.$faker->unique()->email], [
                                'tenant_id' => $tenant->id,
                                'first_name' => $namePrefix.$faker->firstName,
                                'last_name' => $faker->lastName,
                                'password' => bcrypt('secret'),
                                'login_allowed' => true,
                                'company_id' => $company->id,
                            ])->assignRole($role);
                        }

                        for ($i = 1; $i <= 5; $i++) {
                            $report = Report::create([
                                'tenant_id' => $tenant->id,
                                'user_id' => $user->id,
                                'title' => $namePrefix."Report $i",
                                'description' => "Description for Report $i",
                            ]);

                            Appointment::create([
                                'tenant_id' => $tenant->id,
                                'user_id' => $user->id,
                                'report_id' => $report->id,
                                'title' => $namePrefix."Appointment $i",
                                'description' => "Description for Appointment $i",
                                'dt_start' => Carbon::parse($report->date)->setHour(9)->setMinute(0)->setSecond(0),
                                'dt_end' => Carbon::now()->subWeeks($i)->setHour(10)->setMinute(0)->setSecond(0),
                                'created_by' => $user->id
                            ]);
                        }

                        $appointments = [
                            ['weeks' => 2, 'hour' => 9],
                            ['weeks' => 5, 'hour' => 10],
                        ];

                        foreach ($appointments as $setting) {
                            Appointment::create([
                                'tenant_id' => $tenant->id,
                                'user_id' => $user->id,
                                'title' => $namePrefix.'Upcoming Appointment',
                                'description' => 'Future Description',
                                'dt_start' => Carbon::now()->addWeeks($setting['weeks'])->setHour($setting['hour'])->setMinute(0)->setSecond(0),
                                'dt_end' => Carbon::now()->addWeeks($setting['weeks'])->setHour($setting['hour'] + 1)->setMinute(0)->setSecond(0),
                                'created_by' => $user->id
                            ]);
                        }
                    });
                }
            });
            $this->call([
                NotificationTemplateSeeder::class
            ]);
        }
    }
}
