<?php

namespace App\Listeners;

use App\Events\UserActivity;

class UserActivityListener
{
    public function handle(UserActivity $event): void
    {
        $event->user->update([
            'last_activity' => now()->timestamp,
        ]);
    }
}
