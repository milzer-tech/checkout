<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoAvailablePlanDto extends Data
{
    public function __construct(
        public string $PlanCode,
        public int $Ordering,
        public ErgoPlanDetailDto $PlanDetail,
        public ErgoQuoteDto $Quote
    ) {}
}
