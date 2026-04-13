<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapDataCollectionCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Dto;

class ErgoTravelerAllocationsTypeDto extends Dto
{
    public function __construct(
        #[WithCast(ErgoSoapDataCollectionCast::class, ErgoTravelerAllocationTypeDto::class, 'TravelerAllocation', ['ID', 'TravelerIDRef'])]
        public Collection $TravelerAllocation
    ) {}
}
