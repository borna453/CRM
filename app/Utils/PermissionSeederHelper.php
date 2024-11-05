<?php
namespace App\Utils;

use App\Enums\Permissions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeederHelper
{
    public static function assignPermissionsByRole(Role $role, string $tenantId, string $roleType): void
    {
        $permissions = [];

        if ($roleType === 'admin') {
            $permissions = [
                'Admin Resources' => Permissions::groups()['Admin Resources'],
                'Admin Pages' => Permissions::groups()['Admin Pages'],
                'Admin Widgets' => Permissions::groups()['Admin Widgets'],
            ];
        } elseif ($roleType === 'user') {
            $permissions = [
                'User Resources' => Permissions::groups()['User Resources'],
                'User Pages' => Permissions::groups()['User Pages'],
                'User Widgets' => Permissions::groups()['User Widgets'],
            ];
        } elseif ($roleType === 'employee') {
            $permissions = [
                'Admin Resources' => Permissions::groups()['Admin Resources'],
                'Admin Pages' => Permissions::groups()['Admin Pages'],
                'Admin Widgets' => Permissions::groups()['Admin Widgets'],
            ];
        }

        self::assignGroupedPermissions($role, $tenantId, $permissions);
    }

    public static function assignGroupedPermissions(Role $role, string $tenantId, array $groups): void
    {
        foreach ($groups as $permissions) {
            if (is_array($permissions)) {
                self::assignGroupedPermissions($role, $tenantId, $permissions);
            } else {
                if ($permissions instanceof \App\Enums\Permissions) {
                    $permissionName = $permissions->value;

                    // Retrieve the permission for the specific tenant, or create it if it doesn't exist
                    $permission = Permission::firstOrCreate(
                        ['name' => $permissionName, 'tenant_id' => $tenantId],
                        ['guard_name' => 'web']
                    );

                    // Ensure the permission is assigned to the role for this tenant
                    self::assignPermissionToRoleWithTenant($role, $permission, $tenantId);
                }
            }
        }
    }

    protected static function assignPermissionToRoleWithTenant(Role $role, Permission $permission, string $tenantId): void
    {
        // Check if the permission is already assigned to the role for this tenant
        $exists = DB::table('role_has_permissions')
            ->where('permission_id', $permission->id)
            ->where('role_id', $role->id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$exists) {
            // Assign the permission to the role for this tenant
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permission->id,
                'role_id' => $role->id,
                'tenant_id' => $tenantId,
            ]);
        }
    }
}

