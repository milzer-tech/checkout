<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use Illuminate\Support\Facades\Cache;
use Saloon\Http\Response;

final class AvailabilitySupporter
{
    private static string $keyPrefix = 'varifyAvailability-';

    private static string $statusKeyPrefix = 'varifyAvailability-status-';

    /**
     * Cache the result of the availability verification.
     */
    public function cacheResult(string $checkoutId, Response $response): void
    {
        Cache::put(
            key: self::$keyPrefix.$checkoutId,
            value: $response->array(),
            ttl: now()->addMinutes(10)
        );

        Cache::put(
            key: self::$statusKeyPrefix.$checkoutId,
            value: $response->status(),
            ttl: now()->addMinutes(10)
        );
    }

    /**
     * Get the cached result of the availability verification.
     *
     * @return array<string, mixed>
     */
    public function getCachedResult(string $checkoutId): array
    {
        return Cache::get(self::$keyPrefix.$checkoutId, []);
    }

    /**
     * Get the cached status of the availability verification.
     */
    public function getCachedStatus(string $checkoutId): int
    {
        return (int) Cache::get(self::$statusKeyPrefix.$checkoutId, 500);
    }

    /**
     * Clear the cached result and status of the availability verification.
     */
    public function clearCache(string $checkoutId): void
    {
        Cache::forget(self::$keyPrefix.$checkoutId);

        Cache::forget(self::$statusKeyPrefix.$checkoutId);
    }

    /**
     * Check if the availability verification result and status are cached.
     */
    public function has(string $checkoutId): bool
    {
        return Cache::has(self::$keyPrefix.$checkoutId) && Cache::has(self::$statusKeyPrefix.$checkoutId);
    }
}
