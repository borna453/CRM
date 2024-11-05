<?php

use App\Policies\AppointmentPolicy;

it('allows employee to view any appointment when has permission', function () {
    $canView = app(AppointmentPolicy::class)->viewAny($this->adminUser);

    expect($canView)->toBeTrue();
});

it('allows employee to create appointment when has permission', function () {
    $canCreate = app(AppointmentPolicy::class)->create($this->adminUser);

    expect($canCreate)->toBeTrue();
});

it('allows employee to update appointment when has permission', function () {
    $canUpdate = app(AppointmentPolicy::class)->update($this->adminUser);

    expect($canUpdate)->toBeTrue();
});

it('allows employee to delete appointment when has permission', function () {
    $canDelete = app(AppointmentPolicy::class)->delete($this->adminUser);

    expect($canDelete)->toBeTrue();
});

it('allows employee to restore appointment when has permission', function () {
    $canRestore = app(AppointmentPolicy::class)->restore($this->adminUser);

    expect($canRestore)->toBeTrue();
});

it('allows employee to view unbilled appointment when has permission', function () {
    $canViewUnbilled = app(AppointmentPolicy::class)->viewUnbilled($this->adminUser);

    expect($canViewUnbilled)->toBeTrue();
});

it('allows employee to access appointment resource when has permission', function () {
    $this->actingAs($this->adminUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.appointments.index'));

    $response->assertStatus(200);
});
