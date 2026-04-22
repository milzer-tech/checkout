<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoCoveredPersonDto extends Data
{
    public function __construct(
        public ErgoPersonNameDto $PersonName,
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $Birthdate
    ) {}
}
