<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapDataCollectionCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoRequestServicesTypeDto extends Data
{
    public function __construct(
        #[WithCast(ErgoSoapDataCollectionCast::class, ErgoRequestServiceTypeDto::class, 'Service', ['ID', 'QuotedTariff'])]
        public Collection $Service,
    ) {}
}
