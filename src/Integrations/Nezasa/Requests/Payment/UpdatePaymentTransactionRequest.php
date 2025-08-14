<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Payment;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\UpdatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\UpdatePaymentTransactionResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdatePaymentTransactionRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::PATCH;

    /**
     * Create a new instance of CreatePaymentTransactionRequest.
     */
    public function __construct(
        public readonly string $checkoutRefId,
        public readonly string $transactionRefId,
        public readonly UpdatePaymentTransactionPayload $payload
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'payment-transactions/v1.13/'.$this->checkoutRefId.'/update/'.$this->transactionRefId;
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
     * Create a DTO from the response.
     */
    public function createDtoFromResponse(Response $response): UpdatePaymentTransactionResponse
    {
        return UpdatePaymentTransactionResponse::from($response->array());
    }
}
