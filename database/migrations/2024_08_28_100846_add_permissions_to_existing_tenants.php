<?php

use App\Models\Tenant;
use App\Models\User;
use App\Utils\PermissionSeederHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class AddPermissionsToExistingTenants extends Migration
{
    public function up(): void
    {
        Schema::table('', function (Blueprint $table) {
            Tenant::all()->each(function (Tenant $tenant) {
                $tenant->run(function () use ($tenant) {
                    $adminRole = Role::where(['name' => 'admin', 'guard_name' => 'web'])->first();
                    $userRole = Role::where(['name' => 'user', 'guard_name' => 'web'])->first();
                    $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

                    PermissionSeederHelper::assignPermissionsByRole($adminRole, $tenant->id, User::ADMIN);
                    PermissionSeederHelper::assignPermissionsByRole($userRole, $tenant->id, User::USER);
                    PermissionSeederHelper::assignPermissionsByRole($employeeRole, $tenant->id, User::EMPLOYEE);
                });
            });
        });
    }

    public function down(): void
    {
        Schema::table('', function (Blueprint $table) {
            Tenant::all()->each(function (Tenant $tenant) {
                $tenant->run(function () {
                    $employeeRole = Role::where('name', 'employee')->first();

                    if ($employeeRole) {
                        $employeeRole->delete();
                    }
                });
            });
        });
    }
}
