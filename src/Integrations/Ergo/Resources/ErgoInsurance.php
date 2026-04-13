<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Resources;

use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoCreatePreContractualInformationRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsuranceBookRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsurancePlanSearchRQDto;
use Nezasa\Checkout\Integrations\Ergo\Requests\ErgoCreatePreContractualInformation;
use Nezasa\Checkout\Integrations\Ergo\Requests\ErgoInsuranceBook;
use Nezasa\Checkout\Integrations\Ergo\Requests\ErgoPlanSearch;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class ErgoInsurance extends BaseResource
{
    public function planSearch(ErgoInsurancePlanSearchRQDto $payload): Response
    {
        return $this->connector->send(new ErgoPlanSearch($payload));
    }

    public function createPreContractualInformation(ErgoCreatePreContractualInformationRQDto $payload): Response
    {
        return $this->connector->send(new ErgoCreatePreContractualInformation($payload));
    }

    public function book(ErgoInsuranceBookRQDto $payload): Response
    {
        return $this->connector->send(new ErgoInsuranceBook($payload));
    }
}
