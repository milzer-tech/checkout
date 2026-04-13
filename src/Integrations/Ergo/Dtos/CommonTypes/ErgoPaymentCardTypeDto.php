<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Enum\ErgoCardTypeEnum;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoPaymentCardTypeDto extends Data
{
    public function __construct(
        public string $CardHolder,
        public ErgoCardTypeEnum $CardType,
        public string $CardNumber,
        public string $CardNumberAlias,
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $ExpireDate,
        public string $VerificationCode,
        public string $AdditionalPaymentInfo,
        public ErgoPSD2ParameterTypeDto $PSD2Parameter,
        public string $ccSecureHandleID,
        public string $ccPayPageHandleID
    ) {}
}
