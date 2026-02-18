<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Requests;

use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopCapturePaymentPayload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class ComputopCapturePaymentRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of ComputopCapturePaymentRequest
     *
     * @link https://app.swaggerhub.com/apis-docs/Computop/Paygate_REST_API/1#/Payments/capturePayment
     */
    public function __construct(
        public string $paymentId,
        public ComputopCapturePaymentPayload $payload
    ) {}

    /**
     * Get the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "payments/{$this->paymentId}/captures";
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
