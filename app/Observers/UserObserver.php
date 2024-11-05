<?php

namespace App\Observers;

use App\Jobs\UserWelcomeJob;
use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->password = bcrypt(Str::random());
    }

    public function created(User $user): void
    {
        if($user->login_allowed && $user->should_invite){
            UserWelcomeJob::dispatch($user);
        }
    }
}
