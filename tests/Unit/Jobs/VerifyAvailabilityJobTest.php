<?php

use Illuminate\Support\Facades\Cache;
use Mockery as m;
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

    /** @var Response|m\MockInterface $response */
    $response = m::mock(Response::class);
    $response->shouldReceive('array')->once()->andReturn(['available' => true, 'items' => [1, 2]]);
    $response->shouldReceive('status')->once()->andReturn(200);

    /** @var CheckoutResource|m\MockInterface $checkoutApi */
    $checkoutApi = m::mock(CheckoutResource::class);
    $checkoutApi->shouldReceive('varifyAvailability')
        ->once()
        ->with('co-VA-1')
        ->andReturn($response);

    /** @var NezasaConnector|m\MockInterface $connector */
    $connector = m::mock(NezasaConnector::class);
    $connector->shouldReceive('checkout')->andReturn($checkoutApi);
    app()->instance(NezasaConnector::class, $connector);

    (new VerifyAvailabilityJob('co-VA-1'))->handle();

    expect(Cache::get('varifyAvailability-co-VA-1'))
        ->toBe(['available' => true, 'items' => [1, 2]]);

    expect(Cache::get('varifyAvailability-status-co-VA-1'))
        ->toBe(200);
});

it('returns expected uniqueId', function (): void {
    $job = new VerifyAvailabilityJob('xyz');

    expect($job->uniqueId())->toBe('xyz-verify-availability');
});
