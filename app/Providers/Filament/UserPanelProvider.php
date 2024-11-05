<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfile;
use App\Filament\Widgets\OpenTaskWidget;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\UserLocale;
use App\Livewire\Login;
use App\Livewire\UserUpcomingAppointmentsCalendar;
use App\Utils\BrandLogoHelper;
use App\Utils\Filament\Actions\QuickActionsHelper;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Guava\FilamentKnowledgeBase\KnowledgeBasePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
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
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
                Dashboard::class
            ])
            ->widgets([
                UserUpcomingAppointmentsCalendar::class,
                OpenTaskWidget::class,
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
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
                SimpleLightBoxPlugin::make(),
                AdvancedTablesPlugin::make()
                    ->resourceEnabled(false)
                    ->viewManagerActiveViewIndicator()
                    ->favoritesBarDefaultView(false)
                    ->favoritesBarDivider()
                    ->presetViewsManageable(false)
                    ->viewManagerInFavoritesBar(false)
                    ->viewManagerInTable(false)
                    ->viewManagerSlideOver()
                    ->userViewsEnabled(false),
            ])
            ->databaseNotifications()->databaseNotificationsPolling(null);
    }
}
