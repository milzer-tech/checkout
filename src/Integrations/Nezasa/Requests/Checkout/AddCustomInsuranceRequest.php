<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddCustomInsuranceRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of AddCustomInsuranceRequest
     */
    public function __construct(
        public string $checkoutId,
        public AddCustomInsurancePayload $payload
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/checkout/v1/checkouts/'.$this->checkoutId.'/insurances';
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
}
