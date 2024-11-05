<?php

beforeEach(function () {
   \App\Models\Appointment::factory(10)->create([
      'user_id' => $this->regularUser->id,
   ]);
});

it('loads the index page correctly without errors', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.user.resources.appointments.index'));

    $response->assertStatus(200);
});
