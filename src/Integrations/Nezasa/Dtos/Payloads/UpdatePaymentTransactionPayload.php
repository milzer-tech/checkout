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
        public NezasaTransactionStatusEnum $status = NezasaTransactionStatusEnum::Closed,
    ) {}
}
