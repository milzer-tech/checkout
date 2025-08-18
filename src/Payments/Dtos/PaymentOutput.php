<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class PaymentOutput extends BaseDto
{
    /**
     * Create a new instance of PaymentOutput.
     */
    public function __construct(
        public PaymentGatewayEnum $gateway,
        public PaymentStatusEnum $status,
        public array $data = [],
    ) {}
}
