<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapDataCollectionCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoQuoteDto extends Data
{
    public function __construct(
        public int $ID,
        public ErgoServicesTypeDto $Services,
        #[WithCast(ErgoSoapDataCollectionCast::class, ErgoInsuranceDetailDto::class, 'InsuranceDetail', ['Code', 'Title'])]
        public Collection $InsuranceDetails,
        public $AcceptedPaymentTypes
    ) {}
}
