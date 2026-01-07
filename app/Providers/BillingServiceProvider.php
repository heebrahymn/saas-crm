<?php

namespace App\Providers;

use App\Services\Billing\StripeService;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeService::class, function ($app) {
            return new StripeService();
        });
    }

    public function boot(): void
    {
        //
    }
}