<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Requests;

use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurCreateBookingPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\HanseMerkurCreateOffersResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class HanseMerkurCreateBookingRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of HanseMerkurCreateBookingRequest
     */
    public function __construct(public HanseMerkurCreateBookingPayload $payload) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'v1/bookings';
    }

    /**
     * Define the body of the request.
     *
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->payload->toArray();
    }

    public function createDtoFromResponse(Response $response): HanseMerkurCreateOffersResponse
    {
        return HanseMerkurCreateOffersResponse::from($response->json());
    }
}
