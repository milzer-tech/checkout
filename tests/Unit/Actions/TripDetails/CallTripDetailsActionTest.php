<?php

use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\RequiredResponses;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetAvailableUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\TravelerRequirementsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountriesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountryCodesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\AddedRentalCarsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can retrieve a trip details', function (): void {

    MockClient::global([
        GetItineraryRequest::class => MockResponse::fixture('get_itinerary_response'),
        RetrieveCheckoutRequest::class => MockResponse::fixture('retrieve_checkout_response'),
        AddedRentalCarsRequest::class => MockResponse::fixture('added_rental_cars_response'),
        TravelerRequirementsRequest::class => MockResponse::fixture('traveller_requirements_response'),
        GetAvailableUpsellItemsRequest::class => MockResponse::fixture('get_available_upsell_items_response'),
        RetrieveCheckoutUpsellItemsRequest::class => MockResponse::fixture('retrieve_checkout_upsell_items_response'),
        CountryCodesRequest::class => MockResponse::fixture('country_codes_response'),
        CountriesRequest::class => MockResponse::fixture('countries_response'),
    ]);

    $result = (new CallTripDetailsAction)->run('itineraryId', 'checkoutId');

    expect($result)
        ->toBeObject()
        ->toBeInstanceOf(RequiredResponses::class);
});
