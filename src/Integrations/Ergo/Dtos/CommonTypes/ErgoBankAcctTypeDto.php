<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoBankAcctTypeDto extends Data
{
    public function __construct(
        public ?string $BankID = null,
        public ?string $BankAcctNumber = null,
        public ?array $DifferingAccountHolder = null,
        public ?string $bankSecureHandleID = null
    ) {}
}
