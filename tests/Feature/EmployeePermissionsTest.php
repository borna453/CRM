<?php

use App\Policies\AppointmentPolicy;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

it('allows employee to view any appointment when has permission', function () {
    $canView = app(AppointmentPolicy::class)->viewAny($this->employeeUser);

    expect($canView)->toBeTrue();
});

it('allows employee to create appointment when has permission', function () {
    $canCreate = app(AppointmentPolicy::class)->create($this->employeeUser);

    expect($canCreate)->toBeTrue();
});

it('allows employee to update appointment when has permission', function () {
    $canUpdate = app(AppointmentPolicy::class)->update($this->employeeUser);

    expect($canUpdate)->toBeTrue();
});

it('allows employee to delete appointment when has permission', function () {
    $canDelete = app(AppointmentPolicy::class)->delete($this->employeeUser);

    expect($canDelete)->toBeTrue();
});

it('allows employee to restore appointment when has permission', function () {
    $canRestore = app(AppointmentPolicy::class)->restore($this->employeeUser);

    expect($canRestore)->toBeTrue();
});

it('allows employee to view unbilled appointment when has permission', function () {
    $canViewUnbilled = app(AppointmentPolicy::class)->viewUnbilled($this->employeeUser);

    expect($canViewUnbilled)->toBeTrue();
});

it('allows employee to access appointment resource when has permission', function () {
    $this->actingAs($this->employeeUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.appointments.index'));

    $response->assertStatus(200);
});

it('does not allow employee to access admin appointment resource when no permission', function () {
    $this->actingAs($this->employeeUser);

    DB::table('role_has_permissions')
        ->where('permission_id', Permission::where('name', 'appointments.view')->first()->id)
        ->where('role_id', $this->employeeRole->id)
        ->where('tenant_id', $this->tenant->id)
        ->delete();


    $response = $this->get($this->tenant->route('filament.admin.resources.appointments.index'));

    $response->assertStatus(403);
});

it('does not allow employee to access permissions resource', function () {
    $this->actingAs($this->employeeUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.permissions.index'));

    $response->assertStatus(403);
});
