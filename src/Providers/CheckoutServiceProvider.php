<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nezasa\Checkout\Livewire\AdditionalServicesSection;
use Nezasa\Checkout\Livewire\Banner;
use Nezasa\Checkout\Livewire\ConfirmationPage;
use Nezasa\Checkout\Livewire\ContactDetails;
use Nezasa\Checkout\Livewire\PaymentOptionsSection;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Livewire\PromoCodeSection;
use Nezasa\Checkout\Livewire\Stepper;
use Nezasa\Checkout\Livewire\TravelerDetails;
use Nezasa\Checkout\Livewire\TravelInsuranceSection;
use Nezasa\Checkout\Livewire\TripDetailsPage;
use Nezasa\Checkout\Livewire\TripSummary;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(path: __DIR__.'/../Config/checkout.php', key: 'checkout');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadResources();

        $this->registerLivewireComponents();

        $this->setUpConfigurations();

        $this->publishAssets();
    }

    /**
     * Register Livewire components manually.
     */
    private function registerLivewireComponents(): void
    {
        Livewire::component(name: 'stepper', class: Stepper::class);
        Livewire::component(name: 'banner', class: Banner::class);
        Livewire::component(name: 'contact-details', class: ContactDetails::class);
        Livewire::component(name: 'traveler-details', class: TravelerDetails::class);
        Livewire::component(name: 'promo-code-section', class: PromoCodeSection::class);
        Livewire::component(name: 'travel-insurance-section', class: TravelInsuranceSection::class);
        Livewire::component(name: 'additional-services-section', class: AdditionalServicesSection::class);
        Livewire::component(name: 'payment-options-section', class: PaymentOptionsSection::class);
        Livewire::component(name: 'trip-summary', class: TripSummary::class);
        Livewire::component(name: 'trip-details-page', class: TripDetailsPage::class);
        Livewire::component(name: 'payment-page', class: PaymentPage::class);
        Livewire::component(name: 'payment-result-page', class: PaymentResultPage::class);
        Livewire::component(name: 'confirmation-page', class: ConfirmationPage::class);

    }

    /**
     * Load the necessary resources for the package.
     */
    private function loadResources(): void
    {
        $this->loadRoutesFrom(path: __DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(path: __DIR__.'/../Resources/Views', namespace: 'checkout');

        $this->loadTranslationsFrom(path: __DIR__.'/../../lang', namespace: 'checkout');
    }

    /**
     * Set up configurations for the package.
     */
    private function setUpConfigurations(): void
    {
        Config::set(key: 'livewire.layout', value: 'checkout::layouts.layout');

        Config::set(key: 'data.date_format', value: [DATE_ATOM, 'Y-m-d', 'Y-m-d\TH:i:s.uP', 'Y-m-d H:i:sO']);

        Config::set('app.locale', request()->input('lang', 'en'));
    }

    /**
     * Publish package assets.
     */
    private function publishAssets(): void
    {
        $this->publishes(paths: [
            __DIR__.'/../Resources/assets' => resource_path(path: 'vendor/checkout'),
            __DIR__.'/../Resources/config/tailwind.config.js' => base_path(path: 'tailwind.config.js'),
            __DIR__.'/../Resources/config/postcss.config.js' => base_path(path: 'postcss.config.js'),
            __DIR__.'/../../lang' => $this->app->langPath('vendor/checkout'),

        ], groups: 'checkout');

        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'checkout-migrations');
    }
}
