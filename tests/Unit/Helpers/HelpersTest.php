<?php

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;

it('resolves checkout_path to package root and subpaths', function (): void {
    $root = checkout_path();

    expect(is_dir($root))->toBeTrue()
        ->and(checkout_path('src'))->toBe($root.DIRECTORY_SEPARATOR.'src');
});

it('builds checkout params from the current request', function (): void {
    $request = Request::create('/checkout/details', 'GET', [
        'checkoutId' => 'co-200',
        'itineraryId' => 'it-300',
        'origin' => 'ibe',
        'lang' => 'de',
        'restPayment' => '1',
    ]);

    app()->instance('request', $request);

    $dto = checkout_params();

    expect($dto)->toBeInstanceOf(CheckoutParamsDto::class)
        ->and($dto->checkoutId)->toBe('co-200')
        ->and($dto->itineraryId)->toBe('it-300')
        ->and($dto->origin)->toBe('ibe')
        ->and($dto->lang)->toBe('de')
        ->and($dto->restPayment)->toBeTrue();
});
