<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

final class UpsellItemOfferPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of UpsellItemOfferPayloadEntity.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Upsell-Items/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1upsell-items~1offers/put
     */
    public function __construct(
        public string $offerId,
        public ?string $serviceCategoryRefId = null,
        public ?int $quantity = null,
    ) {}
}
