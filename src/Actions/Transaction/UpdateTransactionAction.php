<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Transaction;

use Nezasa\Checkout\Models\Transaction;

final readonly class UpdateTransactionAction
{
    /**
     * Handle updating transaction data.
     *
     * @param  array<string, mixed>  $data
     */
    public function run(Transaction|string $transaction, array $data): bool
    {
        $transaction = is_string($transaction) ? Transaction::find($transaction) : $transaction;

        return $transaction->update($data);
    }
}
