<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\UserWelcomeEmail;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UserWelcomeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private User $user)
    {
    }

    public function handle(): void
    {
        $this->user->notify(new UserWelcomeEmail($this->user));
        $this->user->update([
            'invited_at' => now()
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->user->update([
            'invited_at' => null
        ]);
    }
}
