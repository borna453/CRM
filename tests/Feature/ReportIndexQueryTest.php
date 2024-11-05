<?php

use App\Models\Appointment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function(){
    Report::factory(10)->create([
        'user_id' => $this->regularUser->id,
    ]);
});

it('loads the index page without errors', function () {
    $response = $this->get($this->tenant->route('filament.admin.resources.reports.index'));

    $response->assertStatus(200);
});
