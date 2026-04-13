<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Requests;

use LogicException;
use Nezasa\Checkout\Integrations\Ergo\ErgoConnector;
use Nezasa\Checkout\Integrations\Foundation\Contracts\SoapConnector;
use Nezasa\Checkout\Integrations\Foundation\Request\ParentSaloonRequest;
use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\Repositories\Body\StringBodyRepository;
use Soap\Engine\HttpBinding\SoapResponse;

abstract class ErgoManualSoapRequest extends ParentSaloonRequest
{
    protected Method $method = Method::POST;

    protected string $soapMethod;

    public function boot(PendingRequest $pendingRequest): void
    {
        parent::boot($pendingRequest);
        $connector = $pendingRequest->getConnector();
        if (! $connector instanceof ErgoConnector) {
            throw new LogicException(self::class.' requires '.ErgoConnector::class);
        }

        $xml = $this->buildSoapEnvelope($connector);
        $pendingRequest->setBody(new StringBodyRepository($xml));
        $pendingRequest->headers()->add('Content-Type', 'text/xml; charset=utf-8');
        $action = trim($this->soapMethod, '"\'');
        $pendingRequest->headers()->add('SOAPAction', '"'.$action.'"');
    }

    abstract protected function buildSoapEnvelope(ErgoConnector $connector): string;

    public function resolveEndpoint(): string
    {
        return '';
    }

    protected function decodeSoapResponse(Response $response): mixed
    {
        $connector = $response->getConnector();
        if (! $connector instanceof SoapConnector) {
            throw new LogicException('Connector must implement '.SoapConnector::class);
        }

        return $connector->soapDriver()->decode($this->soapMethod, new SoapResponse($response->body()));
    }
}
