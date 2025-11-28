<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class ComputopAmountDto extends BaseDto
{
    /**
     * Create a new instance of ComputopAmountDto.
     */
    public function __construct(
        // Amount in the smallest currency unit (e.g. EUR Cent).
        public int $value,
        public string $currency
    ) {}

}
