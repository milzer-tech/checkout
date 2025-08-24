<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Models\Checkout;

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
        $response = NezasaConnector::make()->checkout()->varifyAvailability($this->checkoutId);

        if ($response->ok()) {
            Checkout::query()->where('checkout_id', $this->checkoutId)->update([
                'availability_response' => $response->array(),
                'availability_at' => now(),
            ]);
        }
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->checkoutId.'-verify-availability';
    }
}
