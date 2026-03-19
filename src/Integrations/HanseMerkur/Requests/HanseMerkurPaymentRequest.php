<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Requests;

use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurPaymentPayload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class HanseMerkurPaymentRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of HanseMerkurCreateBookingRequest
     */
    public function __construct(public HanseMerkurPaymentPayload $payload) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'v1/payments';
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
