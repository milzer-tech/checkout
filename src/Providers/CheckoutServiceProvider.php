<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nezasa\Checkout\Livewire\AdditionalServicesSection;
use Nezasa\Checkout\Livewire\Banner;
use Nezasa\Checkout\Livewire\ContactDetails;
use Nezasa\Checkout\Livewire\PaymentOptionsSection;
use Nezasa\Checkout\Livewire\PromoCodeSection;
use Nezasa\Checkout\Livewire\Stepper;
use Nezasa\Checkout\Livewire\TravelerDetails;
use Nezasa\Checkout\Livewire\TravelInsuranceSection;
use Nezasa\Checkout\Livewire\TripSummary;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'checkout');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'checkout');

        Livewire::component('stepper', Stepper::class);
        Livewire::component('banner', Banner::class);
        Livewire::component('contact-details', ContactDetails::class);
        Livewire::component('traveler-details', TravelerDetails::class);
        Livewire::component('promo-code-section', PromoCodeSection::class);
        Livewire::component('travel-insurance-section', TravelInsuranceSection::class);
        Livewire::component('additional-services-section', AdditionalServicesSection::class);
        Livewire::component('payment-options-section', PaymentOptionsSection::class);
        Livewire::component('trip-summary', TripSummary::class);

        Config::set('livewire.layout', 'checkout::layouts.layout');
        Config::set('data.date_format', [
            DATE_ATOM,
            'Y-m-d',
            'Y-m-d\TH:i:s.uP',
        ]);

        $this->publishes([
            //            __DIR__ . '/../Resources/assets' => resource_path('vendor/checkout'),
            __DIR__.'/../Resources/assets' => resource_path('vendor/checkout'),
            __DIR__.'/../Resources/config/tailwind.config.js' => base_path('tailwind.config.js'),
            __DIR__.'/../Resources/config/postcss.config.js' => base_path('postcss.config.js'),
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
