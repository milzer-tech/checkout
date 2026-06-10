<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Payment;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentAuthorizationPayload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreatePaymentAuthorizationRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of CreatePaymentAuthorizationRequest.
     */
    public function __construct(
        public readonly string $checkoutRefId,
        public readonly CreatePaymentAuthorizationPayload $payload
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'payment-authorization/v1.13/'.$this->checkoutRefId;
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
}
