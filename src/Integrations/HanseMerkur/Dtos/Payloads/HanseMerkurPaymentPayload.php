<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurPaymentMethodPayloadEntity;

class HanseMerkurPaymentPayload extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurPaymentPayload.
     */
    public function __construct(
        public string $policyNumber,
        public HanseMerkurPaymentMethodPayloadEntity $paymentMethod,
    ) {}
}
