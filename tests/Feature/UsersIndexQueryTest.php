<?php

use App\Models\Appointment;
use App\Models\PinboardItem;
use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    User::factory(10)->create([
        'company_id' => $this->company->id,
    ]);
});

it('loads the index page with no errors', function () {
    $response = $this->get($this->tenant->route('filament.admin.resources.users.index'));

    $response->assertStatus(200);
});
