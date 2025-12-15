<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class hostedPaymentPageEntity extends BaseDto
{
    /**
     * Create a new instance of PayTypePayloadEntity.
     *
     * @param  array<int, string>  $payTypes
     */
    public function __construct(
        public array $payTypes = [
            'CC',
            'PayPal',
            'ApplePay',
            'GooglePay',
        ]
    ) {}

}
