<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsurancePlanSearchRQDto;
use Nezasa\Checkout\Integrations\Ergo\ErgoConnector;
use Nezasa\Checkout\Integrations\Ergo\Soap\ErgoSoapDocumentBuilder;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

beforeEach(function (): void {
    Config::set([
        'checkout.insurance.ergo.base_url' => 'https://egate2.erv.de/esc201909/ESCConnector',
        'checkout.insurance.ergo.crs' => 'ESC_DE',
        'checkout.insurance.ergo.crs_agency' => '033315000000',
        'checkout.insurance.ergo.initiator' => 'customer',
        'checkout.insurance.ergo.agent' => 'customer',
        'checkout.insurance.ergo.locale_country' => 'DE',
        'checkout.insurance.ergo.locale_language' => 'de',
        'checkout.insurance.ergo.locale_currency' => 'EUR',
        'checkout.insurance.ergo.list_type' => 'DE_STANDARD',
        'checkout.insurance.ergo.auto_quote' => true,
        'checkout.insurance.ergo.echo_token' => null,
        'checkout.insurance.ergo.transaction_context' => null,
    ]);

    Carbon::setTestNow(Carbon::parse('2026-04-09 13:26:28', 'UTC'));

    Str::createUuidsUsingSequence([
        Uuid::fromString('178c1149-96ef-4ef2-9d8d-b446be426e4d'),
        Uuid::fromString('0d211940-a70f-4c6c-83a3-a7d2a51be311'),
    ]);
});

afterEach(function (): void {
    Carbon::setTestNow();
    Str::createUuidsNormally();
});

it('encodes ERV_InsurancePlanSearchRQ to the canonical SOAP 1.1 envelope', function (): void {
    $createOffers = new CreateInsuranceOffersDto(
        startDate: CarbonImmutable::parse('2026-07-23'),
        endDate: CarbonImmutable::parse('2026-07-27'),
        totalPrice: new Price(amount: 6950.0, currency: 'EUR'),
        contact: ContactInfoPayloadEntity::from([
            'firstName' => 'Test',
            'lastName' => 'Contact',
            'email' => 'test@example.com',
            'country' => 'DE',
            'countryCode' => 'DE',
            'city' => 'Berlin',
            'postalCode' => '10115',
            'street1' => 'Street 1',
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

    $invoke = new ReflectionMethod(ErgoInsurance::class, 'buildPlanSearchPayload');
    /** @var ErgoInsurancePlanSearchRQDto $payload */
    $payload = $invoke->invoke(new ErgoInsurance, $createOffers);

    $connector = ErgoConnector::make();
    $prepared = $connector->prepareSoapPayload($payload);
    $actual = ErgoSoapDocumentBuilder::insurancePlanSearch($prepared);

    $expected = file_get_contents(__DIR__.'/../../../Fixtures/Ergo/plan_search_expected.xml');
    Assert::assertNotFalse($expected);

    Assert::assertXmlStringEqualsXmlString($expected, $actual);
});
