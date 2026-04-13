<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Dto;

class ErgoTravelerAllocationTypeDto extends Dto
{
    public function __construct(public int $ID, public int $TravelerIDRef, public string $CoInsured = 'false') {}
}
