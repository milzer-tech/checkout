<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoDestinationTypeDto extends Data
{
    public function __construct(
        public array $Country
    ) {}
}
