<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Resources;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\UpdatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\CreatePaymentTransactionRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\DeletePaymentTransactionRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\UpdatePaymentTransactionRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class PaymentTransactionResource extends BaseResource
{
    /**
     * Create a new payment transaction for a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function create(string $checkoutId, CreatePaymentTransactionPayload $payload): Response
    {
        return $this->connector->send(
            new CreatePaymentTransactionRequest($checkoutId, $payload)
        );
    }

    /**
     * Update an existing payment transaction for a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function update(string $checkoutId, string $refId, UpdatePaymentTransactionPayload $payload): Response
    {
        return $this->connector->send(
            new UpdatePaymentTransactionRequest($checkoutId, $refId, $payload)
        );
    }

    /**
     * Delete a payment transaction for a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function delete(string $checkoutId, string $transactionRefId): Response
    {
        return $this->connector->send(
            new DeletePaymentTransactionRequest($checkoutId, $transactionRefId)
        );
    }
}
