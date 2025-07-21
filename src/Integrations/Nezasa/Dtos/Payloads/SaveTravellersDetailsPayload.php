<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;

class SaveTravellersDetailsPayload extends BaseDto
{
    /**
     * Create a new instance of SaveTravellersDetailsPayload.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Traveler-Information/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1traveler-details/post
     *
     * @param  Collection<int, PaxInfoPayloadEntity>  $paxInfo
     */
    public function __construct(
        public ContactInfoPayloadEntity $contactInfo = new ContactInfoPayloadEntity,
        public Collection $paxInfo = new Collection,
    ) {}
}
