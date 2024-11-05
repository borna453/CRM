<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\OwnerPanelProvider::class,
    App\Providers\Filament\SuperAdminPanelProvider::class,
    App\Providers\Filament\UserPanelProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    Barryvdh\Debugbar\ServiceProvider::class,
];
