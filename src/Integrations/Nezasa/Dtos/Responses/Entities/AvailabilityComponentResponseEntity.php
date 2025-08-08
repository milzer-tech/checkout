<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NoneBookableReasonEnum;

class AvailabilityComponentResponseEntity extends BaseDto
{
    /**
     * Create a new instance of AvailabilityComponentResponseEntity.
     */
    public function __construct(
        public string $id,
        public string $componentType,
        public AvailabilityEnum $status,
        public bool $nonBookable,
        public Price $price,
        public bool $isBooked,
        public bool $isOnRequest,
        public bool $isPlaceholder,
        public ?NoneBookableReasonEnum $nonBookableReason = null,
    ) {}
}
