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
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetRequlatoryInformationRequest;
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

function mockFixture(string $name, int $status = 200): MockResponse
{
    $path = __DIR__."/Fixtures/Saloon/{$name}.json";
    $decoded = json_decode(file_get_contents($path), true);
    $body = $decoded['data'] ?? [];

    if (is_string($body)) {
        $parsed = json_decode($body, true);
        $body = $parsed ?? [];
    }

    return MockResponse::make($body, $status);
}

function fakeCarbon(int $year = 2025, int $month = 8, int $day = 27, int $hour = 11, int $minute = 20, int $second = 19): void
{
    Carbon::setTestNow(
        Carbon::create($year, $month, $day, $hour, $minute, $second)
    );
}

function fakeInitialNezasaCalls(): void
{
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
    ], MockResponse::make([], 200));
}

function fakeInitialNotFoundNezasaCalls(): void
{
    MockClient::global([
        GetItineraryRequest::class => mockFixture('get_itinerary_404_response', 404),
        RetrieveCheckoutRequest::class => mockFixture('retrieve_checkout_404_response', 404),
        AddedRentalCarsRequest::class => mockFixture('added_rental_cars_404_response', 404),
        TravelerRequirementsRequest::class => mockFixture('traveller_requirements_404_response', 404),
        GetAvailableUpsellItemsRequest::class => mockFixture('get_available_upsell_items_404_response', 404),
        RetrieveCheckoutUpsellItemsRequest::class => mockFixture('retrieve_checkout_upsell_items_404_response', 404),
        CountryCodesRequest::class => mockFixture('country_codes_404_response', 404),
        CountriesRequest::class => mockFixture('countries_404_response', 404),
        GetRequlatoryInformationRequest::class => MockResponse::make([], 404),
    ], MockResponse::make([], 404));
}
