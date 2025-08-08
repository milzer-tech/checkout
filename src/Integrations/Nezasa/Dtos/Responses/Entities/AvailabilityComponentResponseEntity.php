<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\ComponentEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NoneBookableReasonEnum;

class AvailabilityComponentResponseEntity extends BaseDto
{
    /**
     * Create a new instance of AvailabilityComponentResponseEntity.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Availability/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1availability-check/post
     *
     * @note There are more properties in the response, but we only include the ones we need.
     */
    public function __construct(
        public string $id,
        public ComponentEnum $componentType,
        public AvailabilityEnum $status,
        public bool $nonBookable,
        public Price $price,
        public bool $isBooked,
        public bool $isOnRequest,
        public bool $isPlaceholder,
        public ?NoneBookableReasonEnum $nonBookableReason = null,
    ) {}
}
