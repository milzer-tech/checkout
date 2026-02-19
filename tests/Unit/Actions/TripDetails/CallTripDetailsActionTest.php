<?php

use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Dtos\Planner\RequiredResponses;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetAvailableUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetRequlatoryInformationRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\TravelerRequirementsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountriesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountryCodesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\AddedRentalCarsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Faking\MockClient;

it('can retrieve a trip details', function (): void {
    MockClient::global([
        GetItineraryRequest::class => mockFixture('get_itinerary_response'),
        RetrieveCheckoutRequest::class => mockFixture('retrieve_checkout_response'),
        AddedRentalCarsRequest::class => mockFixture('added_rental_cars_response'),
        TravelerRequirementsRequest::class => mockFixture('traveller_requirements_response'),
        GetAvailableUpsellItemsRequest::class => mockFixture('get_available_upsell_items_response'),
        RetrieveCheckoutUpsellItemsRequest::class => mockFixture('retrieve_checkout_upsell_items_response'),
        CountryCodesRequest::class => mockFixture('country_codes_response'),
        CountriesRequest::class => mockFixture('countries_response'),
        GetRequlatoryInformationRequest::class => mockFixture('regulatory_information_response'),
    ]);

    $result = (new CallTripDetailsAction)->run(new CheckoutParamsDto(
        checkoutId: 'co-td-1',
        itineraryId: 'it-td-1',
        origin: 'app',
    ));

    expect($result)
        ->toBeObject()
        ->toBeInstanceOf(RequiredResponses::class);
});
