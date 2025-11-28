<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\ComputopAmountDto;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\OrderPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\PaymentPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\UrlPayloadEntity;

class ComputopPaymentPayload extends BaseDto
{
    /**
     * Create a new instance of ComputopPaymentPayload.
     *
     * @link https://app.swaggerhub.com/apis-docs/Computop/Paygate_REST_API/1#/hostedPaymentPageSpecificObject
     */
    public function __construct(
        public string $transactionId,
        public ComputopAmountDto $amount,
        public OrderPayloadEntity $order,
        public UrlPayloadEntity $urls,
        public PaymentPayloadEntity $payment = new PaymentPayloadEntity,
        public string $language = 'en',
    ) {}

}
