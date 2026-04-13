<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoSearchTravelerTypeDto extends Data
{
    public function __construct(
        public int $ID,
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $Birthdate,
        public ?int $Age,
        public ?string $IndCoverageReqs,
        public ?ErgoExtensionsTypeDto $Extensions,
    ) {}
}
