<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\UpsellItemsResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

class GetAvailableUpsellItemsRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of RetrieveCheckoutRequest
     */
    public function __construct(protected readonly string $checkoutId) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/upsell-items/offers";
    }

    /**
     * Cast the response to a DTO.
     *
     * @throws Throwable
     */
    public function createDtoFromResponse(Response $response): UpsellItemsResponse
    {
        return UpsellItemsResponse::from($response->array());
    }
}
