<?php

use App\Models\Appointment;
use App\Models\Report;
use Illuminate\Support\Facades\DB;


beforeEach(function(){
    $appointments = Appointment::factory(10)->create([
        'user_id' => $this->regularUser->id,
    ]);
});

it('loads the index page without errors', function () {
    $response = $this->get($this->tenant->route('filament.admin.resources.appointments.list'));

    $response->assertStatus(200);
});
