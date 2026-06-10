<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Payment;

use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentAuthorizationPayload;
use Throwable;

final class CreateNezasaPaymentAuthorizationAction
{
    /**
     * Create a Nezasa payment authorization.
     *
     * @return false|array<string, mixed>
     */
    public function run(string $checkoutId, CreatePaymentAuthorizationPayload $payload): false|array
    {
        try {
            return (array) NezasaConnector::make()
                ->paymentAuthorization()
                ->create($checkoutId, $payload)
                ->array();
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
