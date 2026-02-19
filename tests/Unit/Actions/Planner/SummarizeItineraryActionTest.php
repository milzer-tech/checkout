<?php

use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\AddedRentalCarsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Nezasa\Checkout\Models\Checkout;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Response;

it('summarizes an itinerary', function (): void {
    fakeCarbon();
    $responses = prepare();

    $checkout = Checkout::create([
        'checkout_id' => 'test-checkout-id',
        'itinerary_id' => 'test-itinerary-id',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [],
        'rest_payment' => false,
    ]);

    $result = (new SummarizeItineraryAction)->run(
        $responses['itinerary'],
        $responses['checkout'],
        $responses['addedRentalCars'],
        collect($responses['addedUpsellItems']),
        $checkout
    );

    expect($result)
        ->toBeInstanceOf(ItinerarySummary::class)
        ->and($result->toArray())
        ->toMatchSnapshot();
});

function prepare(): array
{
    MockClient::global([
        GetItineraryRequest::class => mockFixture('get_itinerary_response'),
        RetrieveCheckoutRequest::class => mockFixture('retrieve_checkout_response'),
        AddedRentalCarsRequest::class => mockFixture('added_rental_cars_response'),
        RetrieveCheckoutUpsellItemsRequest::class => mockFixture('retrieve_checkout_upsell_items_response'),
    ]);

    $responses = [];

    NezasaConnector::make()
        ->pool([
            'itinerary' => new GetItineraryRequest('test-itinerary-id'),
            'checkout' => new RetrieveCheckoutRequest('test-checkout-id'),
            'addedRentalCars' => new AddedRentalCarsRequest('test-checkout-id'),
            'addedUpsellItems' => new RetrieveCheckoutUpsellItemsRequest('test-checkout-id'),
        ])
        ->withResponseHandler(function (Response $response, string $key) use (&$responses): void {
            $responses[$key] = $response->dto();
        })
        ->send()
        ->wait();

    return $responses;
}
