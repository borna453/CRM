<?php

namespace App\Providers;

use App\Enums\Permissions;
use App\Events\UserActivity;
use App\Listeners\UserActivityListener;
use App\Models\User;
use App\Observers\CommentObserver;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Guava\FilamentKnowledgeBase\Filament\Panels\KnowledgeBasePanel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Parallax\FilamentComments\Models\FilamentComment;
use SaloonKvk\SaloonKvkServiceProvider;
use SaloonRinkel\SaloonRinkelServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Http\LoginResponse::class
        );

        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $this->app->register(SaloonKvkServiceProvider::class);
        $this->app->register(SaloonRinkelServiceProvider::class);

        KnowledgeBasePanel::configureUsing(
            fn(KnowledgeBasePanel $panel) => $panel->viteTheme('resources/css/theme.css')->guestAccess()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::global-search.after',
            fn(): string => Blade::render('@livewire(\'topbar-action-dropdown\')')
        );

        FilamentColor::register([
            'slate' => Color::Slate,
            'gray' => Color::Gray,
            'neutral' => Color::Neutral,
            'red' => Color::Red,
            'orange' => Color::Orange,
            'amber' => Color::Amber,
            'yellow' => Color::Yellow,
            'lime' => Color::Lime,
            'green' => Color::Green,
            'blue' => Color::Blue,
            'purple' => Color::Purple,
            'pink' => Color::Pink,
        ]);

        Model::preventLazyLoading(!App::isProduction());

        Event::listen(
            UserActivity::class,
            UserActivityListener::class
        );

        Filament::registerNavigationGroups([
            'Administration' => NavigationGroup::make(fn() => __('portal.administration'))->collapsible()->collapsed(),
            'Advanced' => NavigationGroup::make(fn() => __('portal.advanced'))->collapsible()->collapsed(),
            'Settings' => NavigationGroup::make(fn() => __('portal.settings'))->collapsible()->collapsed(),
        ]);

        FilamentComment::observe(CommentObserver::class);

        FilamentAsset::register([
            Css::make('flowbite-css', 'https://unpkg.com/flowbite@1.4.0/dist/flowbite.min.css'),
        ]);

        if (! app()->runningInConsole()) {
            FilamentAsset::register([
                Js::make('marker.io', \Illuminate\Support\Facades\Vite::asset('resources/js/marker.js')),
                Css::make('theme-css', \Illuminate\Support\Facades\Vite::asset('resources/css/theme.css')),
                Js::make('app-js', \Illuminate\Support\Facades\Vite::asset('resources/js/app.js'))->module(),
                Css::make('app-css', \Illuminate\Support\Facades\Vite::asset('resources/css/app.css')),
                Js::make('autofocus.js', Vite::asset('resources/js/autofocus.js'))
            ]);
        }

        DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker) {
            $dateTimePicker
                ->seconds(false)
                ->displayFormat('d-m-Y H:i');
        });

        DatePicker::configureUsing(function (DatePicker $datePicker) {
            $datePicker
                ->native(false)
                ->displayFormat('d-m-Y')
                ->closeOnDateSelection()
                ->placeholder('dd-mm-yyyy');
        });
    }
}
