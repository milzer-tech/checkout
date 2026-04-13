<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

/**
 * Sends a real HTTP request to the ERV test gateway (see config checkout.insurance.ergo.base_url).
 *
 * Run: CHECKOUT_ERGO_LIVE_TEST=1 ./vendor/bin/pest tests/Integration/Ergo/ErgoLivePlanSearchTest.php
 */
it('performs a live ERV InsurancePlanSearch against the configured test endpoint', function (): void {
    Config::set([
        'checkout.insurance.ergo.base_url' => env('CHECKOUT_ERGO_INSURANCE_BASE_URL', 'https://egate2.erv.de/esc201909/ESCConnector'),
        'checkout.insurance.ergo.crs' => env('CHECKOUT_ERGO_INSURANCE_CRS', 'ESC_DE'),
        'checkout.insurance.ergo.crs_agency' => env('CHECKOUT_ERGO_INSURANCE_CRS_AGENCY', '033315000000'),
        'checkout.insurance.ergo.initiator' => env('CHECKOUT_ERGO_INSURANCE_INITIATOR', 'customer'),
        'checkout.insurance.ergo.agent' => env('CHECKOUT_ERGO_INSURANCE_AGENT', 'customer'),
        'checkout.insurance.ergo.locale_country' => env('CHECKOUT_ERGO_INSURANCE_LOCALE_COUNTRY', 'DE'),
        'checkout.insurance.ergo.locale_language' => env('CHECKOUT_ERGO_INSURANCE_LOCALE_LANGUAGE', 'de'),
        'checkout.insurance.ergo.locale_currency' => env('CHECKOUT_ERGO_INSURANCE_LOCALE_CURRENCY', 'EUR'),
        'checkout.insurance.ergo.list_type' => env('CHECKOUT_ERGO_INSURANCE_LIST_TYPE', 'DE_STANDARD'),
        'checkout.insurance.ergo.auto_quote' => filter_var(env('CHECKOUT_ERGO_INSURANCE_AUTO_QUOTE', true), FILTER_VALIDATE_BOOLEAN),
        'checkout.insurance.ergo.echo_token' => env('CHECKOUT_ERGO_INSURANCE_ECHO_TOKEN'),
        'checkout.insurance.ergo.transaction_context' => env('CHECKOUT_ERGO_INSURANCE_TRANSACTION_CONTEXT'),
    ]);

    $dto = new CreateInsuranceOffersDto(
        startDate: CarbonImmutable::parse('2026-07-23'),
        endDate: CarbonImmutable::parse('2026-07-27'),
        totalPrice: new Price(amount: 6950.0, currency: 'EUR'),
        contact: ContactInfoPayloadEntity::from([
            'firstName' => 'Live',
            'lastName' => 'Test',
            'email' => 'live-test@example.com',
            'country' => 'DE',
            'countryCode' => 'DE',
            'city' => 'Berlin',
            'postalCode' => '10115',
            'street1' => 'Teststr 1',
        ]),
        paxInfo: collect([
            PaxInfoPayloadEntity::from([
                'refId' => '1',
                'firstName' => 'Traveler',
                'lastName' => 'One',
                'birthDate' => ['year' => '1999', 'month' => '12', 'day' => '11'],
            ]),
        ]),
        destinationCountries: collect(['NL']),
    );

    $result = (new ErgoInsurance)->getOffers($dto);

    expect($result->meta)->toBeArray()
        ->and($result->isSuccessful || filled($result->errorMessage))->toBeTrue(
            'Expected either SOAP success with offers or a structured errorMessage from ERV.'
        );
})->skip(
    fn (): bool => ! filter_var(getenv('CHECKOUT_ERGO_LIVE_TEST') ?: $_ENV['CHECKOUT_ERGO_LIVE_TEST'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'Set CHECKOUT_ERGO_LIVE_TEST=1 to run this integration test (real network call to ERV).'
);
