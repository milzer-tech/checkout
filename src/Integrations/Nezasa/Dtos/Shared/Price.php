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

    /**
     * Get the formatted amount as a string.
     *
     * For payment method
     */
    public function getPaymentAmount(?string $payment = null): string
    {
        return match ($payment) {
            default => number_format(num: $this->amount, decimals: 2, decimal_separator: '.', thousands_separator: '')
        };
    }
}
