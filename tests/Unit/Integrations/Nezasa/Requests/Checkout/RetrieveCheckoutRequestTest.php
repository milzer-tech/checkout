<?php

use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Saloon\Enums\Method;

it('checks the http method', function (): void {
    $request = new RetrieveCheckoutRequest('checkoutId');

    expect($request->getMethod())->toBe(Method::GET);
});

it('checks the endpoint', function (): void {
    $request = new RetrieveCheckoutRequest('checkoutId');

    expect($request->resolveEndpoint())->toBe('/checkout/v1/checkouts/checkoutId');
});
