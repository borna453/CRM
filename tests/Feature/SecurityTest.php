<?php

it('prevents non-admin users from accessing the admin reports resource', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.reports.index'));

    $response->assertStatus(302);
});

it('prevents non-admin users from accessing the admin appointments resource', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.appointments.index'));

    $response->assertStatus(302);
});

it('prevents non-admin users from accessing the users resource', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.users.index'));

    $response->assertStatus(302);
});

it('prevents non-admin users from accessing the company resource', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.companies.index'));

    $response->assertStatus(302);
});

it('prevents non-admin users from accessing the opportunity resource', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.opportunities.index'));

    $response->assertStatus(302);
});

it('prevents user from accessing non-assigned report', function () {
    $this->actingAs($this->regularUser);

    $randomUser = \App\Models\User::factory()->create()->assignRole(\App\Models\User::USER);

    $report = \App\Models\Report::create([
        'title' => 'Sample Report',
        'description' => 'Detailed description here',
        'user_id' => $randomUser->id,
    ]);


    $response = $this->get($this->tenant->route('filament.user.resources.reports.view', ['record' => $report->id]));

    $response->assertStatus(404);
});

it('prevents user from accessing non-assigned appointment', function () {
    $this->actingAs($this->regularUser);

    $randomUser = \App\Models\User::factory()->create()->assignRole(\App\Models\User::USER);

    $appointment = \App\Models\Appointment::create([
        'title' => 'Sample Appointment',
        'description' => 'Detailed description here',
        'dt_start' => now(),
        'dt_end' => now()->addHour(),
        'user_id' => $randomUser->id,
    ]);

    $response = $this->get($this->tenant->route('filament.user.resources.appointments.view', ['record' => $appointment->id]));

    $response->assertStatus(404);
});

it('prevents user from accessing the edit page of reports', function () {
    $this->actingAs($this->regularUser);

    $randomUser = \App\Models\User::factory()->create();
    $report = \App\Models\Report::create([
        'title' => 'Sample Report',
        'description' => 'Detailed description here',
        'user_id' => $randomUser->id
    ]);

    $response = $this->get($this->tenant->route('filament.admin.resources.reports.edit', ['record' => $report->id]));
    $response->assertStatus(302);
});

it('prevents user from accessing the create page for reports', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.reports.create'));
    $response->assertStatus(302);
});

it('prevents user from accessing the edit page of appointments', function () {
    $this->actingAs($this->regularUser);

    $randomUser = \App\Models\User::factory()->create();
    $appointment = \App\Models\Appointment::create([
        'title' => 'Sample Appointment',
        'description' => 'Detailed description here',
        'dt_start' => now(),
        'dt_end' => now()->addHour(),
        'user_id' => $randomUser->id,
    ]);

    $response = $this->get($this->tenant->route('filament.admin.resources.appointments.edit', ['record' => $appointment->id]));
    $response->assertStatus(302);
});

it('prevents user from accessing the view page for appointments', function () {
    $this->actingAs($this->regularUser);

    $appointment = \App\Models\Appointment::create([
        'title' => 'Sample Appointment',
        'description' => 'Detailed description here',
        'dt_start' => now(),
        'dt_end' => now()->addHour(),
        'user_id' => $this->regularUser->id,
    ]);

    $response = $this->get($this->tenant->route('filament.admin.resources.appointments.view', ['record' => $appointment->id]));
    $response->assertStatus(302);
});

it('prevents user from accessing the create page for users', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.users.create'));
    $response->assertStatus(302);
});

it('prevents user from accessing the edit page for users', function () {
    $this->actingAs($this->regularUser);

    $randomUser = \App\Models\User::factory()->create();

    $response = $this->get($this->tenant->route('filament.admin.resources.users.edit', ['record' => $randomUser->id]));
    $response->assertStatus(302);
});

it('prevents user from accessing the edit page for company', function () {
    $this->actingAs($this->regularUser);

    $company = \App\Models\Company::create([
        'name' => 'Sample Company',
    ]);

    $response = $this->get($this->tenant->route('filament.admin.resources.companies.edit', ['record' => $company->id]));
    $response->assertStatus(302);
});

it('prevents user from accessing the create page for company', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.resources.companies.create'));
    $response->assertStatus(302);
});

it('prevents user from accessing the dashboard', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.admin.pages.dashboard'));
    $response->assertStatus(302);
});
