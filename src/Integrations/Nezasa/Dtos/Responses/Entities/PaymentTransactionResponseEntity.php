<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionTypeEnum;

class PaymentTransactionResponseEntity extends BaseDto
{
    /**
     * Create a new instance of PaymentTransactionResponseEntity.
     *
     * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
     *
     * @note: There are other properties that we do not need to set here.
     */
    public function __construct(
        public string $transactionRefId,
        public string $externalRefId,
        public NezasaTransactionStatusEnum $status,
        public Price $amount,
        public NezasaTransactionTypeEnum $transactionType,
        public NezasaPaymentMethodEnum $paymentMethod,
        public CarbonImmutable $created,
        public CarbonImmutable $valuta,
        public ?string $paymentMethodName = null,
    ) {}
}
