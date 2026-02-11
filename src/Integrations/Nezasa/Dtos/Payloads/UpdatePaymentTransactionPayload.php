<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;

class UpdatePaymentTransactionPayload extends BaseDto
{
    /**
     * Create a new instance of UpdatePaymentTransactionPayload.
     *
     * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
     *
     * @note: There are other properties that we do not need to set here.
     */
    public function __construct(
        // Indicates the status of the transaction.
        // When creating payment transactions only the status "Open" and "Closed" are supported.
        // When updating payment transactions only the status "Closed" is supported.
        // The other statuses may be returned in existing payment transactions.
        public NezasaTransactionStatusEnum $status = NezasaTransactionStatusEnum::Closed,
    ) {}
}
