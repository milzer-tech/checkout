<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class PaymentPrepareData extends BaseDto
{
    /**
     * Create a new instance of PaymentPrepareData.
     */
    public function __construct(
        public ContactInfoPayloadEntity $contact,
        public Price $price,
        public string $checkoutId,
        public string $itineraryId,
        public string $origin,
        public ?string $lang = null,
    ) {}
}
