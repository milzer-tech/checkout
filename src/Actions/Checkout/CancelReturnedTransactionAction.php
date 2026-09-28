<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

class CancelReturnedTransactionAction
{
    /**
     * Cancel the pending transaction the user returned from by cancelling at the payment provider.
     * A payment still completed later on the open provider session is processed as usual.
     */
    public function run(Checkout $checkout, mixed $transactionId): void
    {
        if (! is_string($transactionId) || $transactionId === '') {
            return;
        }

        $checkout->transactions()
            ->whereKey($transactionId)
            ->whereStatus(TransactionStatusEnum::Pending)
            ->update(['status' => TransactionStatusEnum::Canceled]);
    }
}
