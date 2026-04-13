<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Nezasa\Checkout\Integrations\Ergo\Dtos\Enum\ErgoNamePrefixEnum;
use Spatie\LaravelData\Data;

class ErgoPersonNameDto extends Data
{
    public function __construct(public ErgoNamePrefixEnum $NamePrefix, public string $GivenName, public string $Surname) {}
}
