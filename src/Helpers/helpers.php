<?php

use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;

if (! function_exists('checkout_path')) {
    /**
     * Get the root path of the package.
     */
    function checkout_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);

        $path = ltrim($path, '/\\');

        return $path === ''
            ? $root
            : $root.DIRECTORY_SEPARATOR.$path;
    }
}

if (! function_exists('checkout_params')) {
    /**
     * Get the checkout params from the request.
     */
    function checkout_params(): CheckoutParamsDto
    {
        $request = request();

        return new CheckoutParamsDto(
            checkoutId: $request->string('checkoutId'),
            itineraryId: $request->string('itineraryId'),
            origin: $request->string('origin'),
            lang: $request->string('lang')->isEmpty() ? null : $request->string('lang'),
            restPayment: $request->boolean('restPayment')
        );
    }
}
