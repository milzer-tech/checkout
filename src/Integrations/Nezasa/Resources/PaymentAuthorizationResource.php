<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Resources;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentAuthorizationPayload;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\CreatePaymentAuthorizationRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class PaymentAuthorizationResource extends BaseResource
{
    /**
     * Create a new payment authorization for a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function create(string $checkoutId, CreatePaymentAuthorizationPayload $payload): Response
    {
        return $this->connector->send(
            new CreatePaymentAuthorizationRequest($checkoutId, $payload)
        );
    }
}
