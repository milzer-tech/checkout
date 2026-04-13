<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoLocalisationDto extends Data
{
    public function __construct(
        public string $Country,
        public string $Language,
        public string $Currency
    ) {}
}
