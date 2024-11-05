<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfile;
use App\Http\Middleware\CentralDomainOnly;
use App\Http\Middleware\UserLocale;
use App\Livewire\Login;
use App\Utils\BrandLogoHelper;
use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OwnerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('owner')
            ->path('owner')
            ->profile(page: EditProfile::class, isSimple: false)
            ->login(Login::class)
            ->colors([
                'primary' => config('adminpanel.primary'),
                'secondary' => config('adminpanel.secondary'),
            ])
            ->favicon(BrandLogoHelper::favicon())
            ->brandLogo(BrandLogoHelper::brandLogo())
            ->darkModeBrandLogo(BrandLogoHelper::darkModeBrandLogo())
            ->brandLogoHeight('4rem')
            ->sidebarWidth(config('adminpanel.sidebarWidth'))
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Owner/Resources'), for: 'App\\Filament\\Owner\\Resources')
            ->discoverPages(in: app_path('Filament/Owner/Pages'), for: 'App\\Filament\\Owner\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->plugin(\TomatoPHP\FilamentDeveloperGate\FilamentDeveloperGatePlugin::make())
            ->plugin(\TomatoPHP\FilamentArtisan\FilamentArtisanPlugin::make())
            ->discoverWidgets(in: app_path('Filament/Owner/Widgets'), for: 'App\\Filament\\Owner\\Widgets')
            ->middleware([
                CentralDomainOnly::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                UserLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
