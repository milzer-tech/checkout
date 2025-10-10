<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

class PaymentInit extends BaseDto
{
    /**
     * Create a new instance of PaymentInit.
     *
     * @param  BaseDto|array<string, mixed>  $persistentData
     */
    public function __construct(
        public string $gatewayName,
        public bool $isAvailable,
        // This property's content is stored in the database.
        public array|BaseDto $persistentData = [],
    ) {}
}
