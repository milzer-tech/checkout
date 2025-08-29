<?php

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Saloon\Http\Auth\TokenAuthenticator;

it('returns the correct base url', function (): void {
    $newUrl = 'https://oppwa.example.test';

    Config::set('checkout.payment.widget.oppwa.base_url', $newUrl);

    $connector = new OppwaConnector;

    expect($connector->resolveBaseUrl())->toBe($newUrl);
});

it('checks the connect timeout', function (): void {
    $connector = new OppwaConnector;

    expect($connector->getConnectTimeout())->toBe(floatval(60));
});

it('checks the request timeout', function (): void {
    $connector = new OppwaConnector;

    expect($connector->getRequestTimeout())->toBe(floatval(60));
});

it('checks the default headers', function (): void {
    $connector = new OppwaConnector;

    expect($connector->headers()->all())
        ->toBeArray()
        ->toEqualCanonicalizing([
            'Accept' => 'application/json',
        ]);
});

it('checks the default query parameters (entityId)', function (): void {
    Config::set('checkout.payment.widget.oppwa.entity_id', 'entity-123');

    $connector = new OppwaConnector;

    $ref = new ReflectionClass($connector);
    $method = $ref->getMethod('defaultQuery');
    $query = $method->invoke($connector);

    expect($query)->toBeArray()->toEqualCanonicalizing([
        'entityId' => 'entity-123',
    ]);
});

it('checks the default auth', function (): void {
    Config::set('checkout.payment.widget.oppwa.token', 'test-token-xyz');

    $connector = new OppwaConnector;
    $authenticator = $connector->getAuthenticator();

    expect($authenticator)->toBeInstanceOf(TokenAuthenticator::class);

    expect(property_exists($authenticator, 'token'))->toBeTrue();

    expect($authenticator->token ?? null)->toBe('test-token-xyz');
});
