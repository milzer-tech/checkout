<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoTripTypeDto extends Data
{
    public function __construct(
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $StartDate,
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $EndDate,
        public ErgoDestinationTypeDto $Destination,
        #[WithCast(ErgoSoapCarbonCast::class, true)]
        public ?Carbon $BookingConfirmation,
        public ErgoCurrencyAmountGroupDto $TotalTripCost
    ) {}
}
