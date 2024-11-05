<?php

use App\Filament\Resources\UserResource\Pages\ListUsers;
use function Pest\Livewire\livewire;

it('allows an admin to impersonate a user', function () {
    livewire(ListUsers::class)
        ->callTableAction('impersonate', $this->regularUser->id);

    expect(auth()->user()->id)->toBe($this->regularUser->id);
});
