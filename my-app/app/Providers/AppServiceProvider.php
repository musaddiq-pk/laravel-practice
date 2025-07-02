<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Js::make('my-vite-script', vite('resources/js/app.js')),
            Css::make('my-vite-style', vite('resources/css/app.css')),
        ]);

        // Add authentication data for JavaScript
        FilamentView::registerRenderHook(
            'panels::body.start',
            fn (): string => Blade::render('
                <script>
                    window.authUserId = @json(auth()->id());
                    window.isAuthenticated = @json(auth()->check());
                    console.log("Auth data loaded:", { userId: window.authUserId, authenticated: window.isAuthenticated });
                </script>
            ')
        );

        require base_path('routes/channels.php');
    }
}
