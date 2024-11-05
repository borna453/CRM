<?php

use App\Models\PinboardItem;

beforeEach(function () {
    PinboardItem::factory(10)->create([
        'user_id' => $this->regularUser->id,
    ]);
});

it('loads the index page without errors', function () {
    $this->actingAs($this->regularUser);

    $response = $this->get($this->tenant->route('filament.user.resources.pinboard-items.index'));

    $response->assertStatus(200);
});
