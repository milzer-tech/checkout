<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use Illuminate\Support\Facades\Cache;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Saloon\Http\Response;

final class AvailabilitySupporter
{
    private static string $keyPrefix = 'varifyAvailability-';

    private static string $statusKeyPrefix = 'varifyAvailability-status-';

    /**
     * Cache the result of the availability verification.
     */
    public function cacheResult(CheckoutParamsDto $params, Response $response): void
    {
        Cache::put(
            key: self::$keyPrefix.$params->checkoutId.$params->itineraryId,
            value: $response->array(),
            ttl: now()->addMinutes(10)
        );

        Cache::put(
            key: self::$statusKeyPrefix.$params->checkoutId.$params->itineraryId,
            value: $response->status(),
            ttl: now()->addMinutes(10)
        );
    }

    /**
     * Get the cached result of the availability verification.
     *
     * @return array<string, mixed>
     */
    public function getCachedResult(CheckoutParamsDto $params): array
    {
        return Cache::get(self::$keyPrefix.$params->checkoutId.$params->itineraryId, []);
    }

    /**
     * Get the cached status of the availability verification.
     */
    public function getCachedStatus(CheckoutParamsDto $params): int
    {
        return (int) Cache::get(self::$statusKeyPrefix.$params->checkoutId.$params->itineraryId, 500);
    }

    /**
     * Clear the cached result and status of the availability verification.
     */
    public function clearCache(CheckoutParamsDto $params): void
    {
        Cache::forget(self::$keyPrefix.$params->checkoutId.$params->itineraryId);

        Cache::forget(self::$statusKeyPrefix.$params->checkoutId.$params->itineraryId);
    }

    /**
     * Check if the availability verification result and status are cached.
     */
    public function has(CheckoutParamsDto $params): bool
    {
        return Cache::has(self::$keyPrefix.$params->checkoutId.$params->itineraryId)
            && Cache::has(self::$statusKeyPrefix.$params->checkoutId.$params->itineraryId);
    }
}
