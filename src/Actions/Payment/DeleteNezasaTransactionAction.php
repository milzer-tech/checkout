<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Payment;

use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Models\Transaction;
use Throwable;

final class DeleteNezasaTransactionAction
{
    /**
     * Delete a Nezasa transaction.
     */
    public function run(Transaction $transaction): bool
    {
        try {
            return NezasaConnector::make()
                ->paymentTransaction()
                ->delete($transaction->checkout->checkout_id, $transaction->nezasa_transaction_ref_id)
                ->successful();
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
