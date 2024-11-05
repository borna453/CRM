<?php

use App\Models\Task;
use Illuminate\Support\Facades\DB;

beforeEach(function(){
    Task::factory(10)->create([
        'user_id' => $this->regularUser->id,
    ]);
});
it('loads the tasks index page without errors', function () {
    $response = $this->get($this->tenant->route('filament.admin.resources.tasks.index'));

    $response->assertStatus(200);
});
