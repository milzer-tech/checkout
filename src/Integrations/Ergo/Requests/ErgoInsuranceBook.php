<?php

namespace Nezasa\Checkout\Integrations\Ergo\Requests;

use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsuranceBookRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Responses\ErgoInsuranceBookRSDto;
use Nezasa\Checkout\Integrations\Ergo\ErgoConnector;
use Nezasa\Checkout\Integrations\Ergo\Soap\ErgoSoapDocumentBuilder;
use Nezasa\Checkout\Integrations\Ergo\Trait\ErgoRequestTrait;
use Saloon\Http\Response;

class ErgoInsuranceBook extends ErgoManualSoapRequest
{
    use ErgoRequestTrait;

    protected string $soapMethod = 'ERV_InsuranceBookRQ';

    public function __construct(protected ErgoInsuranceBookRQDto $ergoPayload) {}

    protected function buildSoapEnvelope(ErgoConnector $connector): string
    {
        $prepared = $connector->prepareSoapPayload($this->ergoPayload);

        return ErgoSoapDocumentBuilder::insuranceBook($prepared);
    }

    public function createDtoFromResponse(Response $response): ErgoInsuranceBookRSDto
    {
        $wdslResponse = $this->decodeSoapResponse($response);
        $json = json_encode($wdslResponse, JSON_THROW_ON_ERROR);

        return ErgoInsuranceBookRSDto::from(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }
}
