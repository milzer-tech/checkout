<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddOrRemoveUpsellItemsPayload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Throwable;

class AddOrRemoveUpsellItemsRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::PUT;

    /**
     * Create a new instance of RetrieveCheckoutUpsellItemsRequest
     */
    public function __construct(public AddOrRemoveUpsellItemsPayload $payload) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/checkout/v1/checkouts/'.$this->payload->checkoutId.'/upsell-items/offers';
    }

    /**
     * Define the body of the request
     *
     * @return array<string, string|null>
     */
    protected function defaultBody(): array
    {
        return $this->payload->toArray();
    }

    /**
     * Cast the response to a DTO.
     *
     * @throws Throwable
     */
    public function createDtoFromResponse(Response $response): mixed
    {
        return $response->array();
    }
}
