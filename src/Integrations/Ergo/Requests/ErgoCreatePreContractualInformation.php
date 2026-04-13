<?php

namespace Nezasa\Checkout\Integrations\Ergo\Requests;

use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoCreatePreContractualInformationRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Responses\ErgoCreatePreContractualInformationRSDto;
use Nezasa\Checkout\Integrations\Ergo\ErgoConnector;
use Nezasa\Checkout\Integrations\Ergo\Soap\ErgoSoapDocumentBuilder;
use Nezasa\Checkout\Integrations\Ergo\Trait\ErgoRequestTrait;
use Saloon\Http\Response;

class ErgoCreatePreContractualInformation extends ErgoManualSoapRequest
{
    use ErgoRequestTrait;

    protected string $soapMethod = 'ERV_InsuranceCreatePreContractualInformationRQ';

    public function __construct(protected ErgoCreatePreContractualInformationRQDto $ergoPayload) {}

    protected function buildSoapEnvelope(ErgoConnector $connector): string
    {
        $prepared = $connector->prepareSoapPayload($this->ergoPayload);

        return ErgoSoapDocumentBuilder::createPreContractualInformation($prepared);
    }

    public function createDtoFromResponse(Response $response): ErgoCreatePreContractualInformationRSDto
    {
        $wdslResponse = $this->decodeSoapResponse($response);
        $json = json_encode($wdslResponse, JSON_THROW_ON_ERROR);

        return ErgoCreatePreContractualInformationRSDto::from(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }
}
