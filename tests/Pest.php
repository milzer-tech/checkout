<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Illuminate\Support\Carbon;
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

uses(Tests\TestCase::class)->in(__DIR__);

function fakeCarbon(int $year = 2025, int $month = 8, int $day = 27, int $hour = 11, int $minute = 20, int $second = 19): void
{
    Carbon::setTestNow(
        Carbon::create($year, $month, $day, $hour, $minute, $second)
    );
}

function fakeInitialNezasaCalls(): void
{
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
}

function fakeInitialNotFoundNezasaCalls(): void
{
    MockClient::global([
        GetItineraryRequest::class => MockResponse::fixture('get_itinerary_404_response'),
        RetrieveCheckoutRequest::class => MockResponse::fixture('retrieve_checkout_404_response'),
        AddedRentalCarsRequest::class => MockResponse::fixture('added_rental_cars_404_response'),
        TravelerRequirementsRequest::class => MockResponse::fixture('traveller_requirements_404_response'),
        GetAvailableUpsellItemsRequest::class => MockResponse::fixture('get_available_upsell_items_404_response'),
        RetrieveCheckoutUpsellItemsRequest::class => MockResponse::fixture('retrieve_checkout_upsell_items_404_response'),
        CountryCodesRequest::class => MockResponse::fixture('country_codes_404_response'),
        CountriesRequest::class => MockResponse::fixture('countries_404_response'),
    ]);
}
