<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use Illuminate\Support\Facades\Cache;
use Saloon\Http\Response;

final class AvailabilitySupporter
{
    private static string $keyPrefix = 'varifyAvailability-';

    private static string $statusKeyPrefix = 'varifyAvailability-status-';

    public static function cacheResult(string $checkoutId, Response $response): void
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

    public static function getCachedResult(string $checkoutId): array
    {
        return Cache::get(self::$keyPrefix.$checkoutId, []);
    }

    public static function getCachedStatus(string $checkoutId): int
    {
        return Cache::get(self::$statusKeyPrefix.$checkoutId, 500);
    }

    public static function clearCache(string $checkoutId): void
    {
        Cache::forget(self::$keyPrefix.$checkoutId);
        Cache::forget(self::$statusKeyPrefix.$checkoutId);
    }

    public static function has(string $checkoutId): bool
    {
        return Cache::has(self::$keyPrefix.$checkoutId) && Cache::has(self::$statusKeyPrefix.$checkoutId);
    }
}
