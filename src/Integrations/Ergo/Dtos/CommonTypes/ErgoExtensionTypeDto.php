<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoExtensionTypeDto extends Data
{
    public function __construct(
        public string $Key,
        public string $Value
    ) {}
}
