<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class UpsellItemOfferResponseEntity extends BaseDto
{
    /**
     * Create a new instance of UpsellItemsResponse.
     *
     * @see https://docs.dev.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Upsell-Items/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1upsell-items~1offers/get
     *
     * @param  Collection<int, UpsellServiceCategoryResponseDto>  $serviceCategories
     * @param  Collection<int, UpsellPictureResponseDto>  $pictures
     *
     * @note There are other properties in the response, but we only need the ones defined here.
     */
    public function __construct(
        public string $offerId,
        public string $productId,
        public string $name,
        public string $description,
        public Collection $serviceCategories,
        public Collection $pictures = new Collection,
        public bool $optOutPossible = false,
        public ?string $defaultSelection = null,
    ) {}
}
