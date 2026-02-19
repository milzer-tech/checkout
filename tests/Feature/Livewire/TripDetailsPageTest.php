<?php

use Livewire\Livewire;
use Nezasa\Checkout\Livewire\TripDetailsPage;

it('renders the trip details page', function (): void {
    fakeInitialNezasaCalls();

    Livewire::test(TripDetailsPage::class, [
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
    ])
        ->assertOk()
        ->assertSee(trans('checkout::page.trip_details.traveller_details'));
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
