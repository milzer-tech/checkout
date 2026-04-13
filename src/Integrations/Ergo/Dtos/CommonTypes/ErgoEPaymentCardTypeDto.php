<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Nezasa\Checkout\Integrations\Ergo\Dtos\Enum\ErgoCardTypeEnum;
use Spatie\LaravelData\Data;

class ErgoEPaymentCardTypeDto extends Data
{
    public function __construct(
        public string $EPaymentProvider,
        public ErgoCardTypeEnum $CardType,
        public string $TransactionID,
        public string $ReferenceID
    ) {}
}
