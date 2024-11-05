<?php

use App\Enums\Features;
use App\Livewire\UpcomingAppointmentsCalendar;
use App\Policies\AppointmentPolicy;
use App\Policies\ReportPolicy;
use App\Utils\Filament\Actions\QuickActionsHelper;

beforeEach(function () {
    \App\Models\Feature::withoutTenancy()->where('name', Features::APPOINTMENTS_AND_REPORTS->value)->update(['value' => 0]);
});

it('denies access to appointments when the feature is disabled', function () {
    $this->actingAs($this->regularUser);

    $policy = new AppointmentPolicy;

    $response = $this->get($this->tenant->route('filament.user.resources.appointments.index'));
    $result = $policy->viewAny($this->regularUser);


    $this->assertFalse($result);
    $response->assertForbidden();
});

it('denies access to reports when the feature is disabled', function () {
    $this->actingAs($this->regularUser);

    $policy = new ReportPolicy;

    $response = $this->get($this->tenant->route('filament.user.resources.reports.index'));
    $result = $policy->viewAny($this->regularUser);

    $this->assertFalse($result);
    $response->assertForbidden();
});

it('does not show the createAppointment action when the feature is disabled', function () {
    $this->actingAs($this->adminUser);
    Livewire::test(\App\Livewire\TopbarActionDropdown::class)
        ->assertDontSee(__('portal.appointments.create'));
});

it('does not show the UpcomingAppointmentsCalendar widget when the feature is disabled', function () {
    Livewire::test(\Filament\Pages\Dashboard::class)
        ->assertDontSeeLivewire(UpcomingAppointmentsCalendar::class);
});
