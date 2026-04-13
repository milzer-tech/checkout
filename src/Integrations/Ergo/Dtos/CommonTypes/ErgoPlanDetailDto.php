<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoDescriptionURLCollectionCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoPlanDetailDto extends Data
{
    public function __construct(
        public string $Title,
        #[WithCast(ErgoDescriptionURLCollectionCast::class)]
        public Collection $DescriptionURL
    ) {}
}
