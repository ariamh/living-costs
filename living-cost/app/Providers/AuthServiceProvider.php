<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('files', function ($app) {
            return new Filesystem();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('view-city', fn($user) => $user->role === 'admin');
        Gate::define('create-city', fn($user) => $user->role === 'admin');
    }
}
