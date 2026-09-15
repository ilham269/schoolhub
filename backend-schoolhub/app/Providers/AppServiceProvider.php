<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PaymentGatewayInterface;
use App\Services\MidtransService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // app/Providers/AppServiceProvider.php



public function register(): void
{
    $this->app->bind(PaymentGatewayInterface::class, MidtransService::class);
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
