<?php

namespace App\Providers;

use App\Models\City;
use App\Observers\CityObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        City::observe(CityObserver::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
