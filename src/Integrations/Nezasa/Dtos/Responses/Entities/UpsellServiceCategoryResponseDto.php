<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class UpsellServiceCategoryResponseDto extends BaseDto
{
    /**
     * Create a new instance of UpsellServiceCategoryResponseDto.
     *
     * @see https://docs.dev.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Upsell-Items/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1upsell-items~1offers/get
     */
    public function __construct(
        public string $serviceCategoryRefId,
        public string $name,
        public string $priceType,
        public Price $salesPrice,
    ) {}
}
