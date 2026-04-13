<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoInsuranceDetailDto extends Data
{
    public function __construct(
        public string $Code,
        public string $Title,
        public mixed $ProductComponents = null,
    ) {}
}
