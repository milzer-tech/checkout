<?php

declare(strict_types=1);

use Nezasa\Checkout\Actions\Checkout\CancelReturnedTransactionAction;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

function returnedTransaction(Checkout $checkout, TransactionStatusEnum $status = TransactionStatusEnum::Pending): Transaction
{
    return Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Stripe',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => $status,
    ]);
}

it('cancels the pending transaction of the checkout', function (): void {
    $checkout = Checkout::factory()->create();
    $transaction = returnedTransaction($checkout);

    resolve(CancelReturnedTransactionAction::class)->run($checkout, $transaction->id);

    expect($transaction->refresh()->status)->toBe(TransactionStatusEnum::Canceled);
});

it('does not cancel a transaction that is no longer pending', function (): void {
    $checkout = Checkout::factory()->create();
    $transaction = returnedTransaction($checkout, TransactionStatusEnum::Captured);

    resolve(CancelReturnedTransactionAction::class)->run($checkout, $transaction->id);

    expect($transaction->refresh()->status)->toBe(TransactionStatusEnum::Captured);
});

it('does not cancel a transaction of another checkout', function (): void {
    $transaction = returnedTransaction(Checkout::factory()->create());

    $otherCheckout = Checkout::factory()->create(['checkout_id' => 'other-checkout-id']);

    resolve(CancelReturnedTransactionAction::class)->run($otherCheckout, $transaction->id);

    expect($transaction->refresh()->status)->toBe(TransactionStatusEnum::Pending);
});

it('ignores a missing or invalid transaction', function (mixed $transactionId): void {
    $checkout = Checkout::factory()->create();
    $transaction = returnedTransaction($checkout);

    resolve(CancelReturnedTransactionAction::class)->run($checkout, $transactionId);

    expect($transaction->refresh()->status)->toBe(TransactionStatusEnum::Pending);
})->with([null, '', ['array'], 'unknown-id']);
