<?php

namespace App\Providers;

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
    $this->loadMigrationsFrom(database_path('migrations/Seller/Manage_inventory'));
    $this->loadMigrationsFrom(database_path('migrations/Admin/account_registration'));
}
}