<?php

namespace App\Listeners;

use App\Mail\RinkelMail;
use App\Models\Tenant;
use App\Utils\RinkelHelper;
use Illuminate\Support\Facades\Mail;

abstract class BaseRinkelListener
{
    protected function checkKey() {
        $tenant = Tenant::findOrFail(RinkelHelper::findTenantId());
        $key = request()->get('key');

        if ($key == $tenant->rinkel) {
            return true;
        }

        return false;
    }

    protected function setNotification($name, $details)
    {
        Mail::to("job@cloudmazing.nl")->send(new RinkelMail($name, $details));
    }

    protected function checkId($id)
    {
        return $id !== '1c8b83a7c690084224ec3984515bc1a2';
    }
}
