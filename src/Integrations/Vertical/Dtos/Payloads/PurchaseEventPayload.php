<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities\PurchasePaymentMethodPayloadEntity;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities\VerticalCustomerPayloadEntity;

final class PurchaseEventPayload extends BaseDto
{
    /**
     * Create a new instance of PurchaseEventPayload.
     */
    public function __construct(
        public string $quote_id,
        public PurchasePaymentMethodPayloadEntity $payment_method,
        public VerticalCustomerPayloadEntity $customer,
    ) {}

}
