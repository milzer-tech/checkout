<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Resources;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\CreatePaymentTransactionRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class PaymentTransactionResource extends BaseResource
{
    /**
     * * Retrieve a checkout by its ID.
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
}
