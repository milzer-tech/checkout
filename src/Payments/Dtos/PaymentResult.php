<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class PaymentResult extends BaseDto
{
    /**
     * Create a new instance of PaymentResult.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function __construct(
        public string $gatewayName,
        public PaymentStatusEnum $status,
        public array $persistentData = [],
    ) {}
}
