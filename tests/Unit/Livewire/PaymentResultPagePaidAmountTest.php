<?php

declare(strict_types=1);

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Enums\BookingStatusEnum;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

function paymentResultPageForPaidAmountTest(TransactionStatusEnum $status, BookingStatusEnum $bookingStatus): PaymentResultPage
{
    $transaction = Transaction::create([
        'checkout_id' => Checkout::factory()->create()->id,
        'gateway' => 'Credit2000',
        'amount' => 1,
        'currency' => 'USD',
        'status' => $status,
    ]);

    $page = new PaymentResultPage;
    $page->transaction = $transaction;
    $page->output = new PaymentOutput(
        gatewayName: 'Credit2000',
        bookingStatusEnum: $bookingStatus,
        isPaymentSuccessful: $status->isCaptured(),
    );

    return $page;
}

function paymentResultPaidAmount(PaymentResultPage $page): Price
{
    return (new ReflectionMethod(PaymentResultPage::class, 'paidAmount'))->invoke($page);
}

it('shows the transaction amount as paid when the payment was captured', function (): void {
    $paid = paymentResultPaidAmount(
        paymentResultPageForPaidAmountTest(TransactionStatusEnum::Captured, BookingStatusEnum::CompleteSuccess)
    );

    expect($paid->amount)->toEqual(1.0)
        ->and($paid->currency)->toBe('USD');
});

it('shows nothing as paid when the payment was not captured', function (TransactionStatusEnum $status): void {
    $paid = paymentResultPaidAmount(
        paymentResultPageForPaidAmountTest($status, BookingStatusEnum::CompleteFailed)
    );

    expect($paid->amount)->toEqual(0.0)
        ->and($paid->currency)->toBe('USD');
})->with([
    'aborted after a failed booking' => TransactionStatusEnum::Aborted,
    'capture failed' => TransactionStatusEnum::CaptureFailed,
    'authorization failed' => TransactionStatusEnum::AuthorizationFailed,
]);
