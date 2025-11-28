<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Resources;

use Nezasa\Checkout\Integrations\Computop\Requests\ComputopPaymentRequest;
use Nezasa\Checkout\Integrations\Computop\Requests\GetComputopPaymentRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class ComputopPaymentResource extends BaseResource
{
    /**
     * Initiate a payment.
     *
     * @throws \Throwable
     */
    public function init($payload): Response
    {
        return $this->connector->send(
            new ComputopPaymentRequest($payload)
        );
    }

    /**
     * Get a payment details.
     *
     * @throws \Throwable
     */
    public function get(string $paymentId): Response
    {
        return $this->connector->send(
            new GetComputopPaymentRequest($paymentId)
        );
    }
}
