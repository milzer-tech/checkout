<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\Requests;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoPlanSearchInsuranceCustomerDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestorDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoSearchTravelersTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoTripTypeDto;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoInsurancePlanSearchRQDto extends Data
{
    public function __construct(
        public string $MsgId,
        public string $EchoToken,
        public string $TransactionContext,
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $TimeStamp,
        public ErgoTripTypeDto $CoveredTrip,
        public ErgoSearchTravelersTypeDto $Travelers,
        public ?ErgoPlanSearchInsuranceCustomerDto $InsuranceCustomer = null,
        public ?ErgoRequestorDto $Requestor = null,
        public string $Target = 'T',
        public bool $AutoQuote = true,
        public string $ListType = 'DE_STANDARD',
    ) {}

    public function setRequestor(?ErgoRequestorDto $requestor): static
    {
        $this->Requestor = $requestor;

        return $this;
    }
}
