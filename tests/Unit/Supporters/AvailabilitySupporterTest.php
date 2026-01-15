<?php

use Illuminate\Support\Facades\Cache;
use Mockery as m;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Supporters\AvailabilitySupporter;
use Saloon\Http\Response;

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    Cache::clear();
});

it('stores and retrieves availability results from cache', function (): void {
    $params = new CheckoutParamsDto('co-1', 'it-1', 'ibe');
    $response = m::mock(Response::class);
    $response->shouldReceive('array')->andReturn(['ok' => true]);
    $response->shouldReceive('status')->andReturn(202);

    $supporter = new AvailabilitySupporter();
    $supporter->cacheResult($params, $response);

    expect($supporter->has($params))->toBeTrue()
        ->and($supporter->getCachedResult($params))->toBe(['ok' => true])
        ->and($supporter->getCachedStatus($params))->toBe(202);

    $supporter->clearCache($params);

    expect($supporter->has($params))->toBeFalse();
});
