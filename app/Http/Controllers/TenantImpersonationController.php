<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Stancl\Tenancy\Features\UserImpersonation;

class TenantImpersonationController extends Controller
{
    public function __invoke($token): RedirectResponse
    {
        return UserImpersonation::makeResponse($token);
    }
}
