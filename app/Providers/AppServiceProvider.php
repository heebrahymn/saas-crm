<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share current tenant with views (if needed later)
        view()->composer('*', function ($view) {
            $view->with('currentTenant', app()->bound('current_tenant') ? app('current_tenant') : null);
        });
    }
}
