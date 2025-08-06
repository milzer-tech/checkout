<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\AddedUpsellItemResponseEntity;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

class RetrieveCheckoutUpsellItemsRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of RetrieveCheckoutUpsellItemsRequest
     */
    public function __construct(protected readonly string $checkoutId) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/upsell-items";
    }

    /**
     * Cast the response to a DTO.
     *
     * @throws Throwable
     */
    public function createDtoFromResponse(Response $response): mixed
    {
        return AddedUpsellItemResponseEntity::collect($response->array());
    }
}
