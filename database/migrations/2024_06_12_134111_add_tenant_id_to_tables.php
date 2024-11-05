<?php

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    const array TABLES = [
        'appointments',
        'pinboard_items',
        'features',
        'media',
        'opportunities',
        'reports',
        'tasks',
        'filament_email_log',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('tenant_id')
                    ->nullable()
                    ->after('id');

                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')->cascadeOnDelete();
            });
        }

        $settings = Schema::getConnection()->query()
            ->from('settings')
            ->get()
            ->groupBy('group')
            ->mapWithKeys(fn (Collection $rows, string $group) => [
                $group => $rows->mapWithKeys(fn (stdClass $row) => [
                    $row->name => json_decode($row->payload, true)
                ])
            ]);

        if (! app()->environment('testing') && Role::query()->where('name', User::USER)->exists()) {
            // Create a base tenant.
            $tenant = Tenant::query()->create(array_merge([
                'id' => 'cloudmazing',
            ], $settings->toArray()));

            // Create a base tenant.
            $test = Tenant::query()->create(array_merge([
                'id' => 'test',
            ], $settings->toArray()));

            $tenant->domains()->create(['domain' => env('APP_TEST_TENANT_DOMAIN', 'cloudmazing.'.env('APP_CENTRAL_DOMAIN', 'cicrm.lan'))]);
            $test->domains()->create(['domain' => env('APP_TEST_2_TENANT_DOMAIN', 'test.'.env('APP_CENTRAL_DOMAIN', 'cicrm.lan'))]);

            foreach (self::TABLES as $table) {
                Schema::getConnection()->query()->from($table)->update(['tenant_id' => $tenant->id]);
            }

            // Owner user is not specific to a tenant.
            Role::create([
                'name' => User::OWNER,
                'guard_name' => 'web',
            ]);
            User::query()->create([
                'name' => 'Job Wiegant',
                'email' => 'job@cloudmazing.nl',
                'password' => bcrypt('secret'),
                'login_allowed' => true,
            ])->assignRole(User::OWNER);

            $test->run(function () use ($tenant) {
                $namePrefix = 'test ';
                $emailPrefix = 'test.';

                $company = Company::updateOrCreate([
                    'name' => $namePrefix . 'Cloudmazing Software',
                    'address' => 'Grotestraat 26-1',
                    'zip_code' => '7471 BP',
                    'city' => 'Goor',
                    'email' => $emailPrefix . 'info@cloudmazing.nl',
                    'phone_number' => '0547 234 500',
                    'coc_number' => '12345678',
                ]);

                User::updateOrCreate(['email' => $emailPrefix . 'admin@cloudmazing.nl'], [
                    'name' => $namePrefix . 'Admin User',
                    'password' => bcrypt('secret'),
                    'login_allowed' => true,
                    'company_id' => $company->id,
                ])->assignRole(User::ADMIN);

                User::updateOrCreate(['email' => $emailPrefix . 'user@cloudmazing.nl'], [
                    'name' => $namePrefix . User::USER,
                    'password' => bcrypt('secret'),
                    'email_enabled' => false,
                    'login_allowed' => true,
                ])->assignRole(User::USER);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::getConnection() instanceof \Illuminate\Database\SQLiteConnection) {
                    $table->dropForeign(['tenant_id']);
                }

                $table->dropColumn('tenant_id');
            });
        }

        Tenant::query()->delete();
    }
};
