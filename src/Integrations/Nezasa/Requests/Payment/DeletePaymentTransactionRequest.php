<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Payment;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeletePaymentTransactionRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::DELETE;

    /**
     * Create a new instance of DeletePaymentTransactionRequest.
     */
    public function __construct(
        public readonly string $checkoutRefId,
        public readonly string $transactionRefId
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'payment-transactions/v1.13/'.$this->checkoutRefId.'/delete/'.$this->transactionRefId;
    }
}
