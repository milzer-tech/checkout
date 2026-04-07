<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Providers\HanseMerkur\HanseMerkurInsurance;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurOffersRequest;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurPaymentRequest;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurReserveRequest;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

uses()->group('hanse-merkur-insurance');

beforeEach(function (): void {
    Config::set('checkout.insurance.hanse_merkur.active', true);
    Config::set('checkout.insurance.hanse_merkur.offers_base_url', 'https://hm-offers.test');
    Config::set('checkout.insurance.hanse_merkur.payment_base_url', 'https://hm-pay.test');
    Config::set('checkout.insurance.hanse_merkur.api_key', 'test-api-key');
    Config::set('checkout.insurance.hanse_merkur.username', 'user');
    Config::set('checkout.insurance.hanse_merkur.password', 'pass');
    Config::set('checkout.insurance.hanse_merkur.requester_id', 'req-1');
    Config::set('checkout.insurance.hanse_merkur.partner_id', 'part-1');
});

function makeCreateInsuranceOffersDto(): CreateInsuranceOffersDto
{
    $pax = PaxInfoPayloadEntity::from([
        'refId' => 'pax-0',
        'firstName' => 'Jane',
        'lastName' => 'Roe',
        'gender' => GenderEnum::Female,
        'birthDate' => ['day' => 15, 'month' => 3, 'year' => 1991],
        'country' => 'DE-Germany',
        'countryCode' => 'DE',
    ]);

    $contact = ContactInfoPayloadEntity::from([
        'firstName' => 'John',
        'lastName' => 'Doe',
        'gender' => GenderEnum::Male,
        'email' => 'john@example.com',
        'country' => 'DE-Germany',
        'countryCode' => 'DE',
        'postalCode' => '10115',
        'city' => 'Berlin',
        'street1' => 'Main',
        'street2' => '42',
    ]);

    return new CreateInsuranceOffersDto(
        startDate: CarbonImmutable::parse('2025-09-01'),
        endDate: CarbonImmutable::parse('2025-09-10'),
        totalPrice: new Price(amount: 1000.0, currency: 'EUR'),
        contact: $contact,
        paxInfo: collect([$pax]),
        destinationCountries: collect(['DE'])
    );
}

it('implements InsuranceContract static helpers', function (): void {
    Config::set('checkout.insurance.hanse_merkur.active', true);
    expect(HanseMerkurInsurance::isActive())->toBeTrue()
        ->and(HanseMerkurInsurance::getName())->toBe('Hanse Merkur');

    Config::set('checkout.insurance.hanse_merkur.active', false);
    expect(HanseMerkurInsurance::isActive())->toBeFalse();
});

it('getNezasaPayload returns the same payload unchanged', function (): void {
    $payload = new AddCustomInsurancePayload(
        name: 'Test insurance',
        netPrice: new Price(10.0, 'EUR'),
        salesPrice: new Price(12.0, 'EUR'),
        bookingStatus: AvailabilityEnum::Open,
    );
    $subject = new HanseMerkurInsurance;

    expect($subject->getNezasaPayload($payload))->toBe($payload);
});

it('getOffers returns failure result when API is unsuccessful', function (): void {
    fakeCarbon();

    MockClient::global([
        HanseMerkurOffersRequest::class => MockResponse::make([
            'messages' => [
                ['message' => 'First error'],
                ['message' => 'Second error'],
            ],
        ], 422),
    ]);

    $subject = new HanseMerkurInsurance;
    $result = $subject->getOffers(makeCreateInsuranceOffersDto());

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->offers)->toBe([])
        ->and($result->errorMessage)->toBe('First error,Second error')
        ->and($result->meta)->toBeArray();
});

it('getOffers maps successful response to sorted offers, terms, and meta', function (): void {
    fakeCarbon();

    MockClient::global([
        HanseMerkurOffersRequest::class => mockFixture('hanse_merkur_create_offers_success'),
    ]);

    $subject = new HanseMerkurInsurance;
    $result = $subject->getOffers(makeCreateInsuranceOffersDto());

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->offers)->toHaveCount(2)
        ->and($result->errorMessage)->toBeNull();

    /** @var array<int, InsuranceOfferDto> $offers */
    $offers = $result->offers;
    expect($offers[0]->id)->toBe('instance-low')
        ->and($offers[0]->title)->toBe('Low premium')
        ->and($offers[0]->price->amount)->toBe(25.0)
        ->and($offers[0]->price->currency)->toBe('EUR')
        ->and($offers[0]->coverage)->toBe([]);

    expect($offers[1]->id)->toBe('instance-high')
        ->and($offers[1]->title)->toBe('High premium')
        ->and($offers[1]->coverage)->toBe(['Medical assistance']);

    $conditions = $offers[1]->terms->conditions;
    expect($conditions)->toHaveCount(3)
        ->and($conditions[0])->toContain('Informationsblatt')
        ->and($conditions[0])->toContain('https://example.test/ipid.pdf')
        ->and($conditions[1])->toContain('Allgemeine Versicherungsbedingungen')
        ->and($conditions[1])->toContain('https://example.test/avb.pdf')
        ->and($conditions[2])->toContain('https://www.hmrv.de/datenschutz');

    expect($result->meta)->toBeArray();
});

it('bookOffer returns failure when reserve fails and does not call payment', function (): void {
    fakeCarbon();

    $reserveCalled = false;
    $paymentCalled = false;

    MockClient::global([
        HanseMerkurReserveRequest::class => function () use (&$reserveCalled): MockResponse {
            $reserveCalled = true;

            return MockResponse::make(['error' => 'reserve failed'], 400);
        },
        HanseMerkurPaymentRequest::class => function () use (&$paymentCalled): MockResponse {
            $paymentCalled = true;

            return MockResponse::make([], 200);
        },
    ]);

    $createDto = makeCreateInsuranceOffersDto();
    $bookDto = new BookInsuranceOfferDto(
        selectedOffer: new InsuranceOfferDto(
            id: 'instance-low',
            title: 'Low',
            price: new Price(25, 'EUR'),
            coverage: []
        ),
        createdOfferDto: $createDto,
    );

    $subject = new HanseMerkurInsurance;
    $result = $subject->bookOffer($bookDto);

    expect($reserveCalled)->toBeTrue()
        ->and($paymentCalled)->toBeFalse()
        ->and($result->isSuccessful)->toBeFalse()
        ->and($result->confirmationId)->toBeNull()
        ->and($result->data)->toHaveKey('error');
});

it('bookOffer returns success when reserve and payment succeed', function (): void {
    fakeCarbon();

    MockClient::global([
        HanseMerkurReserveRequest::class => MockResponse::make([
            'coveredEvent' => [
                'bookingConfirmationDate' => '2025-08-27T11:20:19+00:00',
                'eventStartDate' => '2025-09-01T00:00:00+00:00',
                'eventEndDate' => '2025-09-10T00:00:00+00:00',
                'totalEventCost' => ['amount' => '1000.00', 'currency' => 'EUR'],
                'destinationCountries' => ['DE'],
            ],
            'insuredPersons' => [],
            'policyDetail' => [
                'policyNumber' => 'POL-999',
                'status' => 'RESERVED',
            ],
        ], 200),
        HanseMerkurPaymentRequest::class => MockResponse::make(['paid' => true], 200),
    ]);

    $createDto = makeCreateInsuranceOffersDto();
    $bookDto = new BookInsuranceOfferDto(
        selectedOffer: new InsuranceOfferDto(
            id: 'instance-low',
            title: 'Low',
            price: new Price(25, 'EUR'),
            coverage: []
        ),
        createdOfferDto: $createDto,
    );

    $subject = new HanseMerkurInsurance;
    $result = $subject->bookOffer($bookDto);

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->confirmationId)->toBe('POL-999')
        ->and($result->data)->toHaveKeys(['reserve_response', 'payment_response'])
        ->and($result->data['reserve_response'])->toBeArray()
        ->and($result->data['payment_response'])->toBeArray();
});

it('bookOffer sets isSuccessful false when payment fails but still exposes confirmationId', function (): void {
    fakeCarbon();

    MockClient::global([
        HanseMerkurReserveRequest::class => MockResponse::make([
            'coveredEvent' => [
                'bookingConfirmationDate' => '2025-08-27T11:20:19+00:00',
                'eventStartDate' => '2025-09-01T00:00:00+00:00',
                'eventEndDate' => '2025-09-10T00:00:00+00:00',
                'totalEventCost' => ['amount' => '1000.00', 'currency' => 'EUR'],
                'destinationCountries' => ['DE'],
            ],
            'insuredPersons' => [],
            'policyDetail' => [
                'policyNumber' => 'POL-888',
                'status' => 'RESERVED',
            ],
        ], 200),
        HanseMerkurPaymentRequest::class => MockResponse::make(['paymentError' => true], 402),
    ]);

    $createDto = makeCreateInsuranceOffersDto();
    $bookDto = new BookInsuranceOfferDto(
        selectedOffer: new InsuranceOfferDto(
            id: 'instance-low',
            title: 'Low',
            price: new Price(25, 'EUR'),
            coverage: []
        ),
        createdOfferDto: $createDto,
    );

    $subject = new HanseMerkurInsurance;
    $result = $subject->bookOffer($bookDto);

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->confirmationId)->toBe('POL-888')
        ->and($result->data['payment_response'])->toBeArray();
});

it('getOffers failure builds empty errorMessage when messages missing', function (): void {
    fakeCarbon();

    MockClient::global([
        HanseMerkurOffersRequest::class => MockResponse::make(['errors' => 'x'], 500),
    ]);

    $subject = new HanseMerkurInsurance;
    $result = $subject->getOffers(makeCreateInsuranceOffersDto());

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->errorMessage)->toBe('');
});
