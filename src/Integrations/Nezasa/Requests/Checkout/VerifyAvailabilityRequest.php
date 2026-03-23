<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Nezasa\Checkout\Exceptions\UnavailableServiceException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\VerifyAvailabilityResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Throwable;

class VerifyAvailabilityRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of VerifyAvailabilityRequest
     */
    public function __construct(public readonly string $checkoutId) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/availability-check";
    }

    /**
     * Cast the response to a DTO.
     */
    public function createDtoFromResponse(Response $response): VerifyAvailabilityResponse
    {
        try {
            if (! $response->successful()) {
                throw new UnavailableServiceException($response->array());
            }

            return VerifyAvailabilityResponse::from($response->array());
        } catch (Throwable $exception) {
            throw new UnavailableServiceException([$exception->getMessage()]);
        }
    }
}
