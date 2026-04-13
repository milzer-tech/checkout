<?php

namespace Nezasa\Checkout\Integrations\Ergo\Requests;

use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsurancePlanSearchRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Responses\ErgoInsurancePlanSearchRSDto;
use Nezasa\Checkout\Integrations\Ergo\ErgoConnector;
use Nezasa\Checkout\Integrations\Ergo\Soap\ErgoSoapDocumentBuilder;
use Nezasa\Checkout\Integrations\Ergo\Trait\ErgoRequestTrait;
use Saloon\Http\Response;

class ErgoPlanSearch extends ErgoManualSoapRequest
{
    use ErgoRequestTrait;

    protected string $soapMethod = 'ERV_InsurancePlanSearchRQ';

    public function __construct(protected ErgoInsurancePlanSearchRQDto $ergoPayload) {}

    protected function buildSoapEnvelope(ErgoConnector $connector): string
    {
        $prepared = $connector->prepareSoapPayload($this->ergoPayload);

        return ErgoSoapDocumentBuilder::insurancePlanSearch($prepared);
    }

    public function createDtoFromResponse(Response $response): ErgoInsurancePlanSearchRSDto
    {
        $wdslResponse = $this->decodeSoapResponse($response);
        $json = json_encode($wdslResponse, JSON_THROW_ON_ERROR);

        return ErgoInsurancePlanSearchRSDto::from(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }
}
