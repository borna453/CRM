<?php

use App\Models\Task;

beforeEach(function(){
   Task::factory(10)->create([
         'user_id' => $this->regularUser->id,
        'dt_is_completed' => null,
   ]);
});

it('loads the index page without errors', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.user.resources.tasks.index'));
    
    $response->assertStatus(200);
});
