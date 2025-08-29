<?php

use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\AddedRentalCarsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

it('summarizes an itinerary', function (): void {
    fakeCarbon();
    $responses = prepare();

    $result = (new SummarizeItineraryAction)->run(
        $responses['itinerary'],
        $responses['checkout'],
        $responses['addedRentalCars'],
        collect($responses['addedUpsellItems'])
    );

    expect($result)
        ->toBeInstanceOf(ItinerarySummary::class)
        ->and($result->toArray())
        ->toMatchSnapshot();
});

function prepare(): array
{
    MockClient::global([
        GetItineraryRequest::class => MockResponse::fixture('get_itinerary_response'),
        RetrieveCheckoutRequest::class => MockResponse::fixture('retrieve_checkout_response'),
        AddedRentalCarsRequest::class => MockResponse::fixture('added_rental_cars_response'),
        RetrieveCheckoutUpsellItemsRequest::class => MockResponse::fixture('retrieve_checkout_upsell_items_response'),
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
