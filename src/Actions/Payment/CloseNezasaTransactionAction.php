<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Payment;

use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\UpdatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Transaction;
use Throwable;

final class CloseNezasaTransactionAction
{
    /**
     * Handle closing a Nezasa transaction. It means that the payment is completed and successful.
     *
     * @return false|array<string, mixed>
     */
    public function run(Transaction $transaction): false|array
    {
        try {
            $payload = new UpdatePaymentTransactionPayload(status: NezasaTransactionStatusEnum::Closed);

            return NezasaConnector::make()
                ->paymentTransaction()
                ->update($transaction->checkout->checkout_id, $transaction->nezasa_transaction_ref_id, $payload)
                ->array('transaction');
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
