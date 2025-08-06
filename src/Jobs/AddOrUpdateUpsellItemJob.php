<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddOrRemoveUpsellItemsPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\UpsellItemOfferPayloadEntity;

class AddOrUpdateUpsellItemJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new instance of AddOrUpdateUpsellItemJob.
     */
    public function __construct(
        public string $checkoutId,
        public string $offerId,
        public string $serviceCategoryRefId,
        public int $quantity,

    ) {}

    /**
     * Handle the job to save the section status.
     */
    public function handle(): void
    {
        dd(
            $this->offerId
        );
        NezasaConnector::make()->checkout()->addOrUpdateUpsellItem(
            checkoutId: $this->checkoutId,
            payload: new AddOrRemoveUpsellItemsPayload(
                selection: new Collection([
                    new UpsellItemOfferPayloadEntity(
                        offerId: $this->offerId,
                        serviceCategoryRefId: $this->quantity === 0 ? null : $this->serviceCategoryRefId,
                        quantity: $this->quantity === 0 ? null : $this->quantity,
                    ),
                ])
            )
        );

        SaveSectionStatusJob::make($this->checkoutId, Section::Summary, true)->handle();
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5(
            $this->checkoutId.$this->offerId.$this->serviceCategoryRefId.$this->quantity
        );
    }
}
