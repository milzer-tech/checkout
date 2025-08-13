<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class PaymentResult extends BaseDto
{
    /**
     * Create a new instance of PaymentResult.
     */
    public function __construct(
        public PaymentGatewayEnum $gateway,
        public PaymentStatusEnum $status,
        public array|BaseDto $data = [],
        public array|BaseDto $persistentData = [],
        public ?string $description = null,
    ) {}
}
