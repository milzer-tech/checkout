<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\UpsellItemOfferResponseEntity;

class UpsellItemsResponse extends BaseDto
{
    /**
     * Create a new instance of UpsellItemsResponse.
     *
     * @see https://docs.dev.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Upsell-Items/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1upsell-items~1offers/get
     *
     * @param  Collection<int, UpsellItemOfferResponseEntity>  $offers
     */
    public function __construct(public Collection $offers) {}
}
