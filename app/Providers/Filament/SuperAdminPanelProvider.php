<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfile;
use App\Http\Middleware\CentralDomainOnly;
use App\Http\Middleware\UserLocale;
use App\Livewire\Login;
use App\Models\User;
use App\Utils\BrandLogoHelper;
use App\Http\Middleware\Authenticate;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Guava\FilamentKnowledgeBase\KnowledgeBasePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('superadmin')
            ->path('superadmin')
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
            ->discoverResources(in: app_path('Filament/SuperAdmin/Resources'), for: 'App\\Filament\\SuperAdmin\\Resources')
            ->discoverPages(in: app_path('Filament/SuperAdmin/Pages'), for: 'App\\Filament\\SuperAdmin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/SuperAdmin/Widgets'), for: 'App\\Filament\\SuperAdmin\\Widgets')
            ->middleware([
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
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
            ])
            ->plugins([
                KnowledgeBasePlugin::make(),
                AdvancedTablesPlugin::make()
                    ->resourceEnabled(false)
                    ->userViewsEnabled(false)
                    ->viewManagerActiveViewIndicator()
                    ->favoritesBarDefaultView(false)
                    ->favoritesBarDivider()
                    ->presetViewsManageable(false)
                    ->viewManagerInFavoritesBar(false)
                    ->viewManagerInTable(false)
                    ->viewManagerSlideOver(),
            ]);
    }
}
