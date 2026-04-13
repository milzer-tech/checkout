<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoInsuranceCustomerTypeDto extends Data
{
    public function __construct(
        public ErgoCustomerNameTypeDto $PersonName,
        public string $Email,
        public ErgoAddressDto $Address,
        public ?ErgoPaymentFormTypeDto $PaymentForm = null,
        public ?string $Telephone = null,
        public ?string $Mobile = null,
        public ?string $Fax = null,
        #[WithCast(ErgoSoapCarbonCast::class, true)]
        public ?Carbon $Birthdate = null,
        public ?ErgoExtensionsTypeDto $Extensions = null,
    ) {}
}
