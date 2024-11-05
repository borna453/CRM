<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::redirect('/user/login', '/login');
    Route::redirect('/admin/login', '/login');

    Route::get('/login', \App\Livewire\Login::class)->name('login');

    Route::get('/impersonate/{token}', \App\Http\Controllers\TenantImpersonationController::class);

    // Broadcasting (remove in v4.0 of tenancy)
    Broadcast::routes();
    include 'channels.php';
});
