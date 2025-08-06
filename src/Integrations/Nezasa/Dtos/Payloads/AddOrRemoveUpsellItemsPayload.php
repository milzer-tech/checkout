<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\UpsellItemOfferPayloadEntity;

class AddOrRemoveUpsellItemsPayload extends BaseDto
{
    /**
     * Create a new instance of AddOrRemoveUpsellItemsPayload.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Upsell-Items/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1upsell-items~1offers/put
     *
     * @param  Collection<int, UpsellItemOfferPayloadEntity>  $selection
     */
    public function __construct(
        public Collection $selection,
    ) {}
}
