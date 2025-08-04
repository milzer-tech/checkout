<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;

class AddOrRemoveUpsellItemsPayload extends BaseDto
{
    /**
     * Create a new instance of AddOrRemoveUpsellItemsPayload.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Upsell-Items/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1upsell-items~1offers/put
     */
    public function __construct(
        public readonly string $checkoutId,
        public readonly string $offerId,
        // The service category ref ID to add the upsell item or null to remove it
        public ?string $serviceCategoryRefId = null,
        public ?int $quantity = null,
    ) {}
}
