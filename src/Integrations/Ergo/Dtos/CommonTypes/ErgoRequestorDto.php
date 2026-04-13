<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoRequestorDto extends Data
{
    public function __construct(
        public string $CRS,
        public string $CRSAgency,
        public string $Initiator,
        public string $Agent,
        public ErgoLocalisationDto $Localisation
    ) {}
}
