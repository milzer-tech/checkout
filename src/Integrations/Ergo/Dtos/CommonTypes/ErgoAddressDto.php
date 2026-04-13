<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoAddressDto extends Data
{
    public function __construct(
        public string $StreetAndNr,
        public string $CityName,
        public string $PostalCode,
        public string $Country,
    ) {}
}
