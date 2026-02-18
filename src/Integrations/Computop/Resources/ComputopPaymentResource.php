<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Resources;

use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopCapturePaymentPayload;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopPaymentPayload;
use Nezasa\Checkout\Integrations\Computop\Requests\ComputopCapturePaymentRequest;
use Nezasa\Checkout\Integrations\Computop\Requests\ComputopCreatePaymentRequest;
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
    public function init(ComputopPaymentPayload $payload): Response
    {
        return $this->connector->send(
            new ComputopCreatePaymentRequest($payload)
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

    /**
     * Capture a payment.
     *
     * @throws \Throwable
     */
    public function capture(string $paymentId, ComputopCapturePaymentPayload $payload): Response
    {
        return $this->connector->send(
            new ComputopCapturePaymentRequest($paymentId, $payload)
        );
    }
}
