<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

const APPLE_PAY_DOMAIN_ASSOCIATION_URL = '/.well-known/apple-developer-merchantid-domain-association';

it('serves the Apple Pay domain association file of Adyen as plain text', function (): void {
    configureAdyen();

    $response = $this->get(APPLE_PAY_DOMAIN_ASSOCIATION_URL);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertHeaderMissing('Set-Cookie');

    expect($response->getContent())
        ->toBe(file_get_contents(__DIR__.'/../../src/Resources/adyen/apple-developer-merchantid-domain-association'))
        ->toMatch('/^[0-9A-F]+$/');
});

it('does not serve the Apple Pay domain association file when Adyen is not active', function (): void {
    configureAdyen();
    Config::set('checkout.integrations.adyen.active', false);

    $this->get(APPLE_PAY_DOMAIN_ASSOCIATION_URL)->assertNotFound();
});
