<?php

use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Nezasa\Checkout\Livewire\TripDetailsPage;

it('renders the trip details page', function (): void {
    fakeInitialNezasaCalls();

    Livewire::withQueryParams([
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
        'rest-payment' => false,
    ])->test(TripDetailsPage::class)
        ->assertViewIs('checkout::blades.index');
});

it('renders all active existing payment methods in the payment options section', function (): void {
    fakeInitialNezasaCalls();

    Config::set('checkout.integrations.oppwa.active', true);
    Config::set('checkout.integrations.oppwa.name', 'oppwa');
    Config::set('checkout.integrations.invoice.active', true);
    Config::set('checkout.integrations.invoice.name', 'Invoice');
    Config::set('checkout.integrations.stripe.active', true);
    Config::set('checkout.integrations.stripe.name', 'Credit Card');
    Config::set('checkout.integrations.computop.active', true);
    Config::set('checkout.integrations.computop.name', 'Computop');
    Config::set('checkout.integrations.computop_token.active', true);
    Config::set('checkout.integrations.computop_token.name', 'Computop - Token');

    Livewire::withQueryParams([
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
        'rest-payment' => false,
    ])->test(TripDetailsPage::class)
        ->assertViewIs('checkout::blades.index')
        ->assertSee('oppwa')
        ->assertSee('Invoice')
        ->assertSee('Credit Card')
        ->assertSee('Computop')
        ->assertSee('Computop - Token');
});

it('renders the EU-PRRL compliance error instead of checkout sections', function (): void {
    fakeInitialNonCompliantNezasaCalls();

    Livewire::withQueryParams([
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
        'rest-payment' => false,
    ])->test(TripDetailsPage::class)
        ->assertViewIs('checkout::blades.prrl-compliance-error')
        ->assertSee('Package tour guidelines not fulfilled')
        ->assertSee('Go back to planner')
        ->assertDontSee('Contact details')
        ->assertDontSee('Payment options');
});

it('continues checkout when itinerary content validation is disabled', function (): void {
    fakeInitialContentValidationDisabledNezasaCalls();

    Livewire::withQueryParams([
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
        'rest-payment' => false,
    ])->test(TripDetailsPage::class)
        ->assertViewIs('checkout::blades.index')
        ->assertDontSee('Package tour guidelines not fulfilled');
});

it('renders not-found page', function (): void {
    fakeInitialNotFoundNezasaCalls();

    $this->get(getRoute())->assertNotFound();
});

function getRoute(): string
{
    return route('traveler-details', [
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
    ]);
}
