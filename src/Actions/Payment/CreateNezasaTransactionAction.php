<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Payment;

use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Throwable;

final class CreateNezasaTransactionAction
{
    /**
     * Create a Nezasa transaction.
     *
     * @return false|array<string, mixed>
     */
    public function run(string $checkoutId, CreatePaymentTransactionPayload $payload): false|array
    {
        try {
            return (array) NezasaConnector::make()
                ->paymentTransaction()
                ->create($checkoutId, $payload)
                ->array();
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
