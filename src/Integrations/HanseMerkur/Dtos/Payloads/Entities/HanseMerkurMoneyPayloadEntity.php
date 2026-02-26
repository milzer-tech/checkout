<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class HanseMerkurMoneyPayloadEntity extends BaseDto
{
    /**
     * Represents a monetary value.
     */
    public function __construct(
        // numeric value out of bounds (<8 digits>.<2 digits> expected
        public string $amount,
        public string $currency,
    ) {
        $this->amount = number_format(
            floatval($this->amount), 2, '.', ''
        );
    }

}
