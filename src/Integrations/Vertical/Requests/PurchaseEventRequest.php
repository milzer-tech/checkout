<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Vertical\Requests;

use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\PurchaseEventPayload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class PurchaseEventRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of ComputopPaymentRequest
     */
    public function __construct(public PurchaseEventPayload $payload) {}

    /**
     * Get the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'v1/purchase/travel';
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
    public function hasRequestFailed(Response $response): bool
    {
        return $response->status() !== 200;
    }
}
