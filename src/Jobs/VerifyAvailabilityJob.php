<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Facades\AvailabilityFacade;
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

        AvailabilityFacade::cacheResult($this->checkoutId, $response);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->checkoutId.'-verify-availability';
    }
}
