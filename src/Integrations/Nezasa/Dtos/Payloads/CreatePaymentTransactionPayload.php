<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionTypeEnum;

class CreatePaymentTransactionPayload extends BaseDto
{
    /**
     * Create a new instance of CreatePaymentTransactionPayload.
     *
     * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
     *
     * @note: There are other properties that we do not need to set here.
     */
    public function __construct(
        public string $externalRefId,
        public Price $amount,
        public NezasaPaymentMethodEnum $paymentMethod,
        public NezasaTransactionStatusEnum $status = NezasaTransactionStatusEnum::Open,
        public NezasaTransactionTypeEnum $transactionType = NezasaTransactionTypeEnum::Payment,
        // Must only be used in combination with the payment method "Other" in which case it is mandatory.
        public ?string $paymentMethodName = null,

    ) {}
}
