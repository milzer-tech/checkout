<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapDataCollectionCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoExtensionsTypeDto extends Data
{
    public function __construct(
        #[WithCast(ErgoSoapDataCollectionCast::class, ErgoExtensionTypeDto::class, 'Extension', ['Key', 'Value'])]
        public Collection $Extension,
    ) {}
}
