<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoServiceTypeDto extends Data
{
    public function __construct(
        public ?int $ID,
        public $Tariff,
        public $TravelerAllocations,
        public $ServiceTotalPremium,
        public $ServiceTotalFirstPremium,
        public $ServiceTotalRenewalPremium,
        public ?int $SourceServiceID
    ) {}
}
