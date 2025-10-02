<?php

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Handlers\WidgetInitiationHandler;

it('validates the given gateway in the handler', function () {
    $prepareData = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity,
        price: new Price(amount: 100.00, currency: 'USD'),
        checkoutId: 'chk_123456',
        itineraryId: 'itn_123456',
        origin: 'https://example.com',
    );

    $handler = new WidgetInitiationHandler;

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, []);

    $handler->run(new Checkout, $prepareData, PaymentGatewayEnum::Oppwa);
})->throws(InvalidArgumentException::class, 'The payment gateway is not supported.');

it('validates the given gateway that implements the correct interface', function () {
    $prepareData = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity,
        price: new Price(amount: 100.00, currency: 'USD'),
        checkoutId: 'chk_123456',
        itineraryId: 'itn_123456',
        origin: 'https://example.com',
    );

    $handler = new WidgetInitiationHandler;

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, [
        PaymentGatewayEnum::Oppwa->value => stdClass::class,
    ]);

    $handler->run(new Checkout, $prepareData, PaymentGatewayEnum::Oppwa);
})->throws(InvalidArgumentException::class, 'The gateway does not implement PaymentInitiation.');
