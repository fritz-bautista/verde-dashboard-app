<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;


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
    public function boot(UrlGenerator $url): void
    {
        // Other boot logic...

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        if (config('app.env') === 'production') {
            $url->forceScheme('https');
        }
    }
}
