<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaymentTransactionResponseEntity;

class CreatePaymentTransactionResponse extends BaseDto
{
    /**
     * Create a new instance of CreatePaymentTransactionResponse.
     *
     * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
     */
    public function __construct(
        public PaymentTransactionResponseEntity $transaction,
    ) {}
}
