<?php

use App\Models\User;

it('sends a user welcome email with the correct content', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_enabled' => true,
        'login_allowed' => true,
        'should_invite' => true,
    ]);

    Notification::assertSentTo($user, \App\Notifications\UserWelcomeEmail::class,
    function ($notification) use ($user){
        return Str::contains($notification->getEmailContent(), $user->email);
    });
});
