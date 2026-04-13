<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\Requests;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapCarbonCast;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCoveredTravelersDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoEmailsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoExtensionsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoInsuranceCustomerTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestorDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestServicesTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoTripTypeDto;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoInsuranceBookRQDto extends Data
{
    public function __construct(
        public string $MsgId,
        #[WithCast(ErgoSoapCarbonCast::class)]
        public Carbon $TimeStamp,
        public string $PreContractualInformationID,
        public ErgoCoveredTravelersDto $CoveredTravelers,
        public ?string $QuoteIDRef,
        public ErgoTripTypeDto $CoveredTrip,
        public ErgoInsuranceCustomerTypeDto $InsuranceCustomer,
        public ErgoRequestServicesTypeDto $BookServices,
        public ErgoEmailsTypeDto $EmailPolicy,
        public ?ErgoRequestorDto $Requestor = null,
        public string $Target = 'T',
        public ?ErgoExtensionsTypeDto $Extensions = null,
    ) {}

    public function setRequestor(?ErgoRequestorDto $requestor): static
    {
        $this->Requestor = $requestor;

        return $this;
    }
}
