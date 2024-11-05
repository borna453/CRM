<?php

use App\Policies\AppointmentPolicy;
use Spatie\Permission\Models\Permission;

it('allows user to view any appointment when has permission', function () {
    $canView = app(AppointmentPolicy::class)->viewAny($this->regularUser);

    expect($canView)->toBeTrue();
});

it('allows user to access appointment resource when has permission', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.user.resources.appointments.index'));

    $response->assertStatus(200);
});

it('does not allow user to access admin appointment resource when no permission', function () {
    $this->actingAs($this->regularUser);

    DB::table('role_has_permissions')
        ->where('permission_id', Permission::where('name', 'user_appointments.view')->first()->id)
        ->where('role_id', $this->userRole->id)
        ->where('tenant_id', $this->tenant->id)
        ->delete();

    $response = $this->get($this->tenant->route('filament.user.resources.appointments.index'));

    $response->assertStatus(403);
});
