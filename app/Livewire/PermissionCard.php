<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionCard extends Component
{
    public string $title;
    public array $permissions;
    public string $roleName;
    public string $tenantId;

    public array $checkedPermissions = [];
    public bool $allSelected = false;

    public function mount(string $title, array $permissions, string $roleName, string $tenantId)
    {
        $this->title = $title;
        $this->permissions = $permissions;
        $this->roleName = $roleName;
        $this->tenantId = $tenantId;

        $this->loadCheckedPermissions();
    }

    public function loadCheckedPermissions()
    {
        $role = Role::where('name', $this->roleName)->first();

        $this->checkedPermissions = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $role->id)
            ->where('role_has_permissions.tenant_id', $this->tenantId)
            ->pluck('permissions.name')
            ->toArray();

        $this->allSelected = $this->areAllPermissionsSelected();
    }

    public function togglePermission(string $permissionName)
    {
        $role = Role::where('name', $this->roleName)->first();
        $permission = Permission::where('name', $permissionName)
            ->where('tenant_id', $this->tenantId)
            ->first();

        if (in_array($permissionName, $this->checkedPermissions)) {
            DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->where('role_id', $role->id)
                ->where('tenant_id', $this->tenantId)
                ->delete();
        } else {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permission->id,
                'role_id' => $role->id,
                'tenant_id' => $this->tenantId,
            ]);
        }

        $this->loadCheckedPermissions();
    }

    public function toggleAll()
    {
        $role = Role::where('name', $this->roleName)->first();

        if ($this->allSelected) {
            $this->revokeAllPermissions($role);
        } else {
            $this->assignAllPermissions($role);
        }

        // Reload the checked permissions and update the state
        $this->loadCheckedPermissions();
    }

    private function areAllPermissionsSelected(): bool
    {
        $permissionNames = array_column($this->permissions, 'value');
        return count(array_intersect($permissionNames, $this->checkedPermissions)) === count($this->permissions);
    }

    private function revokeAllPermissions(Role $role)
    {
        $permissionIds = Permission::whereIn('name', array_column($this->permissions, 'value'))
            ->where('tenant_id', $this->tenantId)
            ->pluck('id')
            ->toArray();

        DB::table('role_has_permissions')
            ->where('role_id', $role->id)
            ->where('tenant_id', $this->tenantId)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }

    private function assignAllPermissions(Role $role)
    {
        $permissionRecords = Permission::whereIn('name', array_column($this->permissions, 'value'))
            ->where('tenant_id', $this->tenantId)
            ->get(['id', 'name']);

        foreach ($permissionRecords as $permission) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permission->id,
                'role_id' => $role->id,
                'tenant_id' => $this->tenantId,
            ]);
        }
    }

    public function formatPermissionLabel(string $permission): string
    {
        $segments = explode('.', $permission);

        return isset($segments[1]) ? ucfirst(str_replace('_', ' ', $segments[1])) : ucfirst($segments[0]);
    }

    public function render()
    {
        return view('livewire.permission-card');
    }
}
