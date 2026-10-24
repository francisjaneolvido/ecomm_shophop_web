<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Cloudflare terminates HTTPS before forwarding requests to the local Caddy server.
        // Production route(), asset(), redirects and POST form actions must stay HTTPS.
        // Local/test environments retain their ordinary URL schemes.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->loadMigrationsFrom(database_path('migrations/Seller/Manage_inventory'));
        $this->loadMigrationsFrom(database_path('migrations/Admin/account_registration'));
        $this->loadMigrationsFrom(database_path('migrations/Buyer'));

        // A new station hub automatically covers the city in its own address.
        \App\Models\Logistics\SortingCenter::observe(\App\Observers\SortingCenterObserver::class);
    }
}