<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared;

use Nezasa\Checkout\Dtos\BaseDto;

class Price extends BaseDto
{
    /**
     * Create a new instance of PriceEntity.
     */
    public function __construct(
        public float $amount,
        public string $currency,
    ) {}
}
