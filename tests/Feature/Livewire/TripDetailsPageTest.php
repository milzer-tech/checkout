<?php

it('renders the trip details page', function (): void {
    fakeInitialNezasaCalls();

    $this->get(getRoute())->assertOk();
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
