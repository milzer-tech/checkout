<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class UpsellItemOfferResponseEntity extends BaseDto
{
    /**
     * Create a new instance of UpsellItemsResponse.
     *
     * @see https://docs.dev.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Upsell-Items/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1upsell-items~1offers/get
     *
     * @note There are other properties in the response, but we only need the ones defined here.
     */
    public function __construct(
        public string $offerId,
        public string $productId,
        public string $name,
        public string $description,
        public bool $optOutPossible = false,
        public ?string $defaultSelection = null,
    ) {}
}
