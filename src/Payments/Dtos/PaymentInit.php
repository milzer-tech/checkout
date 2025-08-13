<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;

class PaymentInit extends BaseDto
{
    public function __construct(
        public PaymentGatewayEnum $gateway,
        public bool $isAvailable,
        public array|BaseDto $data = [],
        // This property's content is stored in the database.
        public array|BaseDto $persistentData = [],
    ) {}
}
