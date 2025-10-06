<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;

class VerifyAvailabilityJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly string $checkoutId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = resolve(NezasaConnector::class)->checkout()->varifyAvailability($this->checkoutId);

        Cache::put(
            key: 'varifyAvailability-'.$this->checkoutId,
            value: $response->array(),
            ttl: now()->addMinutes(10)
        );

        Cache::put(
            key: 'varifyAvailability-status-'.$this->checkoutId,
            value: $response->status(),
            ttl: now()->addMinutes(10)
        );
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->checkoutId.'-verify-availability';
    }
}
