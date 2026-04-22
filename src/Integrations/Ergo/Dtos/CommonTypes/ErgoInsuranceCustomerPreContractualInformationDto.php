<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoInsuranceCustomerPreContractualInformationDto extends Data
{
    public function __construct(
        public ErgoCustomerNameTypeDto $PersonName,
        public string $Email,
        public ErgoAddressDto $Address,
        public ?string $Telephone,
        public ?string $Mobile,
        public ?string $Fax,
    ) {}
}
