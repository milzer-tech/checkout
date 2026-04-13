<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Foundation\Request;

use LogicException;
use Nezasa\Checkout\Integrations\Foundation\Contracts\SoapConnector;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Traits\Body\HasXmlBody;
use Soap\Engine\HttpBinding\SoapResponse;

abstract class SoapSaloonRequest extends ParentSaloonRequest implements HasBody
{
    use HasXmlBody;

    public const string SOAP11 = '1.1';

    public const string SOAP12 = '1.2';

    private string $endpoint = '';

    private string $soapVersion = self::SOAP11;

    private string $soapAction = '';

    protected Method $method = Method::POST;

    protected string $soapMethod;

    public function boot(PendingRequest $pendingRequest): void
    {
        parent::boot($pendingRequest);
        $connector = $pendingRequest->getConnector();
        if (! $connector instanceof SoapConnector) {
            throw new LogicException('Connector must implement '.SoapConnector::class);
        }

        $request = $connector->soapDriver()->encode($this->soapMethod, [$connector->prepareSoapPayload($this->soapPayload())]);

        $this->soapVersion = $request->isSOAP11() ? self::SOAP11 : self::SOAP12;
        $this->endpoint = $request->getLocation();
        $this->soapAction = $request->getAction();
        $pendingRequest->setMethod($this->method);
        $pendingRequest->setBody(new StringBodyRepository($request->getRequest()));
        foreach ($this->prepareHeaders() as $key => $value) {
            $pendingRequest->headers()->add($key, $value);
        }
    }

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $connector = $response->getConnector();
        if (! $connector instanceof SoapConnector) {
            throw new LogicException('Connector must implement '.SoapConnector::class);
        }

        $soapDriver = $connector->soapDriver();

        return $soapDriver->decode($this->soapMethod, new SoapResponse($response->body()));
    }

    /**
     * @return array<string, string>
     */
    private function prepareHeaders(): array
    {
        if ($this->soapVersion === self::SOAP11) {
            return $this->prepareSoap11Headers();
        }

        return $this->prepareSoap12Headers();
    }

    /**
     * @return array<string, string>
     */
    private function prepareSoap11Headers(): array
    {
        return ['SOAPAction' => $this->prepareQuotedSoapAction($this->soapAction), 'Content-Type' => 'text/xml; charset="utf-8"'];
    }

    /**
     * @return array<string, string>
     */
    private function prepareSoap12Headers(): array
    {
        $headers = [];
        if ($this->method !== Method::POST) {
            $headers['Accept'] = 'application/soap+xml';

            return $headers;
        }

        $soapAction = $this->prepareQuotedSoapAction($this->soapAction);
        $headers['Content-Type'] = 'application/soap+xml; charset="utf-8"; action='.$soapAction;

        return $headers;
    }

    private function prepareQuotedSoapAction(string $soapAction): string
    {
        $soapAction = trim($soapAction, '"\'');

        return '"'.$soapAction.'"';
    }

    abstract protected function soapPayload(): mixed;
}
