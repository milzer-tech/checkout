<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;

final class AddCustomInsurancePayload extends BaseDto
{
    /**
     * Create a new instance of AddCustomInsurancePayload.
     */
    public function __construct(
        public string $name,
        public Price $netPrice,
        public Price $salesPrice,
        public AvailabilityEnum $bookingStatus,
        public ?string $supplierConfirmationNumber = null,
        public ?string $description = null,

    ) {}

}
