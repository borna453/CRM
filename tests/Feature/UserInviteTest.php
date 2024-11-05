<?php

use App\Filament\Resources\UserResource\Pages\CreateUser;
use function Pest\Livewire\livewire;

it('invites a user after creating with toggle on', function () {
    Notification::fake();


    $this->get($this->tenant->route('filament.admin.resources.users.create'))->assertSuccessful();

    $formArray = [
        'first_name' => 'Test',
        'last_name' => 'User1',
        'email' => 'test@user.nl',
        'company_id' => $this->company->id,
        'login_allowed' => true,
        'email_enabled' => true,
        'should_invite' => true,
    ];

    livewire(CreateUser::class)
        ->assertFormExists()
        ->assertFormFieldExists('first_name')
        ->fillForm($formArray)
        ->assertFormSet($formArray)
        ->call('create');

    $user = \App\Models\User::where('email', 'test@user.nl')->first();

    Notification::assertSentTo($user, \App\Notifications\UserWelcomeEmail::class,
        function ($notification) use ($user) {
            return Str::contains($notification->getEmailContent(), $user->email);
        });
});

it('invites a user after clicking on the table action', function (){
    Notification::fake();

    livewire(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
        ->callTableAction('invite', $this->regularUser->id);

    Notification::assertSentTo($this->regularUser, \App\Notifications\UserWelcomeEmail::class,
        function ($notification)  {
            return Str::contains($notification->getEmailContent(), $this->regularUser->email);
        });
});
