<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class PaymentAuthorizationCardPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of PaymentAuthorizationCardPayloadEntity.
     */
    public function __construct(
        public string $alias,
        public string $brand,
        public string $issuer,
        public string $cardHolderName,
        public int $expiryMonth,
        public int $expiryYear,
    ) {}
}
