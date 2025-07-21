<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Models\Checkout;

class SaveTraverDetailsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $checkoutId,
        public string $name,
        public mixed $value,
        public array $paxInfo
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = Checkout::query()->firstOrCreate(['checkout_id' => $this->checkoutId]);

        $model->updateData($this->name, $this->value);

        $model->refresh();

        if (collect($this->paxInfo)->flatten(1)->reject()->isEmpty()) {
            dd(NezasaConnector::make()
                ->checkout()
                ->saveTravelerDetails($this->checkoutId, $model)
                ->array());
        }
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->checkoutId.'-'.$this->name;
    }
}
