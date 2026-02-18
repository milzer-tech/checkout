<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\ComputopAmountDto;

class ComputopCapturePaymentPayload extends BaseDto
{
    /**
     * Create a new instance of ComputopPaymentPayload.
     *
     * @link https://app.swaggerhub.com/apis-docs/Computop/Paygate_REST_API/1#/Payments/capturePayment
     */
    public function __construct(
        public string $transactionId,
        public ComputopAmountDto $amount,
    ) {}

}
