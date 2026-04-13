<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoPaymentFormTypeDto extends Data
{
    public function __construct(
        public ?ErgoPaymentCardTypeDto $PaymentCard = null,
        public ?ErgoBankAcctTypeDto $BankAcct = null,
        public ?ErgoEPaymentDto $EPayment = null,
    ) {}
}
