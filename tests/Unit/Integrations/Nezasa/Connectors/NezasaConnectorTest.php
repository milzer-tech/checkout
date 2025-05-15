<?php

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Resources\CheckoutResource;
use Saloon\Http\Auth\BasicAuthenticator;

it('returns the correct base url', function () {
    $newUrl = 'https://my.tripbuilder.app';

    Config::set('checkout.nezasa.base_url', $newUrl);

    $connector = new NezasaConnector;

    expect($connector->resolveBaseUrl())->toBe($newUrl);
});

it('checks the connect timeout', function () {
    $connector = new NezasaConnector;

    expect($connector->getConnectTimeout())->toBe(floatval(30));
});

it('checks the request timeout', function () {
    $connector = new NezasaConnector;

    expect($connector->getRequestTimeout())->toBe(floatval(30));
});

it('checks the default headers', function () {
    $connector = new NezasaConnector;

    expect($connector->headers()->all())
        ->toBeArray()
        ->toEqualCanonicalizing([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
});

it('checks the default auth', function () {
    Config::set('checkout.nezasa.username', 'testUsername');
    Config::set('checkout.nezasa.password', 'testPassword');

    $connector = new NezasaConnector;
    $authenticator = $connector->getAuthenticator();

    expect($authenticator)
        ->toBeInstanceOf(BasicAuthenticator::class)
        ->and($authenticator->username)
        ->toBe('testUsername')
        ->and($authenticator->password)
        ->toBe('testPassword');
});

it('returns the checkout resource', function () {
    $connector = new NezasaConnector;

    expect($connector->checkout())->toBeInstanceOf(CheckoutResource::class);
});
