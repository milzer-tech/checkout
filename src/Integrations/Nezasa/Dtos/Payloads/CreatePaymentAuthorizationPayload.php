<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaymentAuthorizationCardPayloadEntity;

class CreatePaymentAuthorizationPayload extends BaseDto
{
    /**
     * Create a new instance of CreatePaymentAuthorizationPayload.
     */
    public function __construct(
        public string $aliasProvider,
        public string $schemeReferenceId,
        public PaymentAuthorizationCardPayloadEntity $card,
    ) {}
}
