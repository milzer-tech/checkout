<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapDataCollectionCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoSearchTravelersTypeDto extends Data
{
    public function __construct(
        #[WithCast(ErgoSoapDataCollectionCast::class, ErgoSearchTravelerTypeDto::class, 'Traveler', ['ID', 'Birthdate'])]
        public Collection $Traveler,
    ) {}
}
