<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Requests;

use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopPaymentPayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class ComputopPaymentRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of ComputopPaymentRequest
     */
    public function __construct(public ComputopPaymentPayload $payload) {}

    /**
     * Get the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'payments';
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
     * Determine if the request has failed based on the response.
     */
    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->status() !== 201;
    }
    //
    //    /**
    //     * Cast the response to a DTO.
    //     */
    //    public function createDtoFromResponse(Response $response): OppwaPrepareResponse
    //    {
    //        return OppwaPrepareResponse::from($response->array());
    //    }
}
