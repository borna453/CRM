<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\User;
use App\Notifications\CompanyCreationReminder;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCompanyCreationReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Company $company, private User $user)
    {
    }

    public function handle()
    {
        if ($this->company->hasIncompleteDetails()){
            $this->user->notify(new CompanyCreationReminder($this->company));

            event(new DatabaseNotificationsSent($this->user));
        }
    }
}
