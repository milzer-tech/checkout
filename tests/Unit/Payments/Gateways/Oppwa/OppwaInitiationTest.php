<?php

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\AddressEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaPrepareRequest;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaInitiation;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('prepares payment successfully and returns PaymentInit with OppwaPrepareResponse persistent data', function () {
    MockClient::global([
        OppwaPrepareRequest::class => MockResponse::fixture('oppwa_prepare_response'),
    ]);

    $data = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            address: new AddressEntity(
                country: 'DE-Deutschland',
                city: 'Berlin',
                postalCode: '10115',
                street1: 'Main St 1'
            ),
        ),
        price: new Price(amount: 475.00, currency: 'EUR'),
        checkoutId: 'chk_123',
        itineraryId: 'itn_123',
        origin: 'https://example.com',
        lang: 'en'
    );

    $gateway = new OppwaInitiation;
    $init = $gateway->prepare($data);

    expect($init)
        ->toBeInstanceOf(PaymentInit::class)
        ->and($init->gateway)->toBe(PaymentGatewayEnum::Oppwa)
        ->and($init->isAvailable)->toBeTrue()
        ->and($init->persistentData)->not()->toBeEmpty();
});

it('returns isAvailable=false when prepare call fails', function () {
    MockClient::global([
        OppwaPrepareRequest::class => MockResponse::make('server-error', 500),
    ]);

    $data = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity(
            firstName: 'Jane',
            lastName: 'Smith',
            email: 'jane@example.com',
            address: new AddressEntity(
                country: 'DE-Deutschland',
                city: 'Munich',
                postalCode: '80331',
                street1: 'Street 2'
            ),
        ),
        price: new Price(amount: 100.00, currency: 'EUR'),
        checkoutId: 'chk_456',
        itineraryId: 'itn_456',
        origin: 'https://example.org'
    );

    $gateway = new OppwaInitiation;

    $init = $gateway->prepare($data);

    expect($init->isAvailable)->toBeFalse()
        ->and($init->persistentData)->toBe([]);
});

it('getAssets throws when persistentData is not OppwaPrepareResponse', function () {
    $gateway = new OppwaInitiation;

    $init = new PaymentInit(
        gateway: PaymentGatewayEnum::Oppwa,
        isAvailable: true,
        persistentData: []
    );

    $gateway->getAssets($init, 'https://return.url');
})->throws(Exception::class, 'The persistent data is not correct');

it('getAssets returns PaymentAsset with expected script and form', function () {
    MockClient::global([
        OppwaPrepareRequest::class => MockResponse::fixture('oppwa_prepare_response'),
    ]);

    $data = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            address: new AddressEntity(
                country: 'DE-Deutschland',
                city: 'Berlin',
                postalCode: '10115',
                street1: 'Main St 1'
            ),
        ),
        price: new Price(amount: 475.00, currency: 'EUR'),
        checkoutId: 'chk_123',
        itineraryId: 'itn_123',
        origin: 'https://example.com'
    );

    $gateway = new OppwaInitiation;
    $init = $gateway->prepare($data);

    $returnUrl = 'https://example.com/return?sig=abc';

    $asset = $gateway->getAssets($init, $returnUrl);

    expect($asset)
        ->toBeInstanceOf(PaymentAsset::class)
        ->and($asset->isAvailable)->toBeTrue()
        ->and($asset->gateway)->toBe(PaymentGatewayEnum::Oppwa)
        ->and($asset->html)
        ->toContain('<form action="'.$returnUrl.'" class="paymentWidgets"')
        ->and($asset->scripts)->not()->toBeEmpty();

    $script = $asset->scripts->first();
    expect($script)
        ->toContain('paymentWidgets.js?checkoutId=')
        ->toContain('integrity="')
        ->toContain('crossorigin="anonymous"');
});

it('getNezasaTransactionPayload throws when persistentData is not OppwaPrepareResponse', function () {
    $gateway = new OppwaInitiation;

    $data = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity,
        price: new Price(amount: 10.00, currency: 'USD'),
        checkoutId: 'chk_1',
        itineraryId: 'itn_1',
        origin: 'https://origin.test'
    );

    $init = new PaymentInit(
        gateway: PaymentGatewayEnum::Oppwa,
        isAvailable: true,
        persistentData: []
    );

    $gateway->getNezasaTransactionPayload($data, $init);
})->throws(Exception::class, 'The persistent data is not correct');

it('getNezasaTransactionPayload returns Nezasa payload with expected values', function () {
    MockClient::global([
        OppwaPrepareRequest::class => MockResponse::fixture('oppwa_prepare_response'),
    ]);

    $data = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            address: new AddressEntity(
                country: 'DE-Deutschland',
                city: 'Berlin',
                postalCode: '10115',
                street1: 'Main St 1'
            ),
        ),
        price: new Price(amount: 475.00, currency: 'EUR'),
        checkoutId: 'chk_123',
        itineraryId: 'itn_123',
        origin: 'https://example.com'
    );

    $gateway = new OppwaInitiation;
    $init = $gateway->prepare($data);

    $payload = $gateway->getNezasaTransactionPayload($data, $init);

    expect($payload->externalRefId)->toBe($init->persistentData->id)
        ->and($payload->amount->amount)->toBe($data->price->amount)
        ->and($payload->amount->currency)->toBe($data->price->currency);
});
