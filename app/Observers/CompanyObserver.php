<?php

namespace App\Observers;

use App\Jobs\SendCompanyCreationReminder;
use App\Models\Company;
use Illuminate\Notifications\DatabaseNotification;

class CompanyObserver
{
    public function created(Company $company): void
    {
        if($company->hasIncompleteDetails()){
            SendCompanyCreationReminder::dispatch($company, auth()->user())->delay(now()->addMinutes(30));
        }
    }

    public function deleted(Company $company): void
    {
        DatabaseNotification::where('data->viewData->model_type', Company::class)
            ->where('data->viewData->model_id', $company->id)
            ->delete();
    }
}
