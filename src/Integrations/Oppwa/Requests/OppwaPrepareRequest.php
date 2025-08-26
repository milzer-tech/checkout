<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Requests;

use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasFormBody;

class OppwaPrepareRequest extends Request implements HasBody
{
    use HasFormBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of ApplyPromoCodeRequest
     */
    public function __construct(public OppwaPreparePayload $payload) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'v1/checkouts';
    }

    /**
     * Define the body of the request.
     *
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return $this->payload->toArray();
    }

    /**
     * Cast the response to a DTO.
     */
    public function createDtoFromResponse(Response $response): OppwaPrepareResponse
    {
        return OppwaPrepareResponse::from($response->array());
    }
}
