<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\SaveTravellersDetailsPayload;
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
        public mixed $value

    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = $this->updateCheckoutModel();

        // If the value is a contact info, update the contact info in the model
        $this->updateTravelerDetailsOnNezasa($model);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->checkoutId.'-'.$this->name;
    }

    /**
     * Update the Checkout model with the provided name and value.
     */
    private function updateCheckoutModel(): Checkout
    {
        $model = Checkout::query()->firstOrCreate(['checkout_id' => $this->checkoutId]);

        $model->updateData([$this->name => $this->value]);

        return $model->refresh();
    }

    public function updateTravelerDetailsOnNezasa(Checkout $model): void
    {
        $paxInfo = new Collection;

        foreach (collect($model->data['paxInfo'] ?? [])->flatten(1) as $index => $pax) {
            $paxInfo[] = PaxInfoPayloadEntity::from([
                'refId' => "pax-$index",
                ...$pax,
            ]);
        }

        if ($model['data']['numberOfPax'] == $paxInfo->count() && isset($model->data['contact'])) {
            $payload = new SaveTravellersDetailsPayload(
                contactInfo: ContactInfoPayloadEntity::from($model->data['contact']),
                paxInfo: $paxInfo
            );

            NezasaConnector::make()->checkout()->saveTravelerDetails($this->checkoutId, $payload);
        }
    }
}
