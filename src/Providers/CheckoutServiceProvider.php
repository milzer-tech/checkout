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
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/checkout.php', 'checkout');
    }
}
