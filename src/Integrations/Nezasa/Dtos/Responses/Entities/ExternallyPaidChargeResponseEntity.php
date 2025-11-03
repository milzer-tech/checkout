<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class ExternallyPaidChargeResponseEntity extends BaseDto
{
    /**
     * Create a new instance of ExternallyPaidChargeResponseEntity.
     */
    public function __construct(
        public string $name,
        public string $productName,
        public Price $value,
    ) {}
}
