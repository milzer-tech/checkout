<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'checkout');

        Config::set('livewire.layout', 'checkout::layouts.layout');
        Config::set('data.date_format', [
            DATE_ATOM,
            'Y-m-d',
            'Y-m-d\TH:i:s.uP',
        ]);

        $this->publishes([
            __DIR__.'/../Resources/Views/assets' => public_path('vendor/checkout'),
        ], 'checkout');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/checkout.php', 'checkout');
    }
}
