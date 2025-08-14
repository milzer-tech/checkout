<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaymentTransactionResponseEntity;

class UpdatePaymentTransactionResponse extends BaseDto
{
    /**
     * Create a new instance of UpdatePaymentTransactionResponse.
     *
     * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
     */
    public function __construct(
        public PaymentTransactionResponseEntity $transaction,
    ) {}
}
