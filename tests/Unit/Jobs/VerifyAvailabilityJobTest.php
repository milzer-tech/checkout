<?php

use Illuminate\Support\Facades\Cache;
use Mockery as m;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Facades\AvailabilityFacade;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Resources\CheckoutResource;
use Nezasa\Checkout\Jobs\VerifyAvailabilityJob;
use Saloon\Http\Response;

afterEach(function (): void {
    m::close();
    Cache::clear();
});

it('stores availability response and status in cache', function (): void {
    config(['cache.default' => 'array']);
    Cache::clear();

    $params = new CheckoutParamsDto('co--1', 'it-11', 'app', 'en');

    /** @var Response|m\MockInterface $response */
    $response = m::mock(Response::class);
    $response->shouldReceive('array')->once()->andReturn(['available' => true, 'items' => [1, 2]]);
    $response->shouldReceive('status')->once()->andReturn(200);

    // Ensure the facade method is called, but let it execute real logic to store in cache
    AvailabilityFacade::partialMock()
        ->shouldReceive('cacheResult')
        ->withArgs([$params, $response])
        ->once()
        ->passthru();

    /** @var CheckoutResource|m\MockInterface $checkoutApi */
    $checkoutApi = m::mock(CheckoutResource::class);
    $checkoutApi->shouldReceive('varifyAvailability')
        ->once()
        ->with($params->checkoutId)
        ->andReturn($response);

    /** @var NezasaConnector|m\MockInterface $connector */
    $connector = m::mock(NezasaConnector::class);
    $connector->shouldReceive('checkout')->andReturn($checkoutApi);
    app()->instance(NezasaConnector::class, $connector);

    (new VerifyAvailabilityJob($params))->handle();

    // Assert cached values with correct keys
    expect(Cache::get('varifyAvailability-co--1it-11'))
        ->toBe(['available' => true, 'items' => [1, 2]]);

    expect(Cache::get('varifyAvailability-status-co--1it-11'))
        ->toBe(200);
});

it('returns expected uniqueId', function (): void {
    $params = new CheckoutParamsDto('xyz', 'it-1', 'app', 'en');
    $job = new VerifyAvailabilityJob($params);

    expect($job->uniqueId())->toBe(md5($params->toJson().'-verify-availability'));
});
