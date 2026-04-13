<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\Requests;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCoveredTravelersDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoEmailsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoInsuranceCustomerPreContractualInformationDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestorDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestServicesTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoTripTypeDto;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoCreatePreContractualInformationRQDto extends Data
{
    public function __construct(
        public string $MsgId,
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $TimeStamp,
        public ErgoCoveredTravelersDto $CoveredTravelers,
        public ?string $QuoteIDRef,
        public ErgoTripTypeDto $CoveredTrip,
        public ErgoInsuranceCustomerPreContractualInformationDto $InsuranceCustomerPreContractualInformation,
        public ErgoRequestServicesTypeDto $PreContractualInformationServices,
        public ErgoEmailsTypeDto $EmailPreContractualInformation,
        public ?ErgoRequestorDto $Requestor = null,
        public string $Target = 'T',
    ) {}

    public function setRequestor(?ErgoRequestorDto $requestor): static
    {
        $this->Requestor = $requestor;

        return $this;
    }
}
