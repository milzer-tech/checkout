<?php

use Livewire\Livewire;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Models\Checkout;

it('redirects direct payment access when EU-PRRL compliance blocks checkout', function (): void {
    fakeInitialNonCompliantNezasaCalls();

    Checkout::create([
        'checkout_id' => 'checkout-456',
        'itinerary_id' => 'itinerary-123',
        'origin' => 'APP',
        'lang' => 'en',
        'rest_payment' => false,
        'data' => [
            'status' => Checkout::buildSectionStatus(),
        ],
    ]);

    $params = [
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
        'rest-payment' => false,
    ];

    Livewire::withQueryParams($params)
        ->test(PaymentPage::class)
        ->assertRedirect(route('traveler-details', [
            'checkoutId' => 'checkout-456',
            'itineraryId' => 'itinerary-123',
            'origin' => 'APP',
            'lang' => 'en',
            'rest-payment' => false,
        ]));
});
