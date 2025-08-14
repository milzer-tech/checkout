<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Payment;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CreatePaymentTransactionResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreatePaymentTransactionRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of CreatePaymentTransactionRequest.
     */
    public function __construct(
        public readonly string $checkoutRefId,
        public readonly CreatePaymentTransactionPayload $payload
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'payment-transactions/v1.13/'.$this->checkoutRefId.'/create';
    }

    /**
     * Define the body of the request.
     *
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        $data = $this->payload->toArray();

        $data['amount']['amount'] = $data['amount']['value'] = $this->payload->amount->getPaymentAmount();

        return $data;
    }

    /**
     * Create a DTO from the response.
     */
    public function createDtoFromResponse(Response $response): CreatePaymentTransactionResponse
    {
        return CreatePaymentTransactionResponse::from($response->array());
    }
}
