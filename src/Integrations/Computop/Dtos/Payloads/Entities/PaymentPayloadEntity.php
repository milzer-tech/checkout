<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class PaymentPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of PaymentPayloadEntity.
     */
    public function __construct(
        public string $method = 'hostedPaymentPage',
        public hostedPaymentPageEntity $hostedPaymentPage = new hostedPaymentPageEntity,
    ) {}

}
