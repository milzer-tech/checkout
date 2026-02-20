<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Http\Request;
use Nezasa\Checkout\Actions\Checkout\BookItineraryAction;
use Nezasa\Checkout\Actions\Checkout\FindBookingResultAction;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Payment\CloseNezasaTransactionAction;
use Nezasa\Checkout\Actions\Transaction\UpdateTransactionAction;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Enums\BookingStatusEnum;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Saloon\Http\Response;

abstract readonly class PaymentCallBackHandler
{
    /**
     * Create a new instance of PaymentCallBackHandler.
     */
    public function __construct(
        protected BookItineraryAction $bookItineraryAction,
        protected UpdateTransactionAction $updateTransactionAction,
        protected CloseNezasaTransactionAction $closeNezasaTransactionAction,
        protected FindBookingResultAction $bookingResultAction,
    ) {}

    /**
     * Handle the payment callback process.
     */
    abstract public function run(Transaction $transaction, Request $request): PaymentOutput;

    /**
     * Validate if the payment gateway is supported and implemented correctly.
     */
    protected function getCallBackClass(string $gateway): PaymentContract
    {
        $result = collect(resolve(GetPaymentProviderAction::class)->run())
            ->where('name', $gateway)
            ->firstOrFail()
            ->decryptClassName();

        /** @phpstan-ignore-next-line */
        return new $result;
    }

    /**
     * Handle the payment abort process.
     */
    protected function handlePaymentAbort(PaymentContract $gateway, Transaction $transaction): void
    {
        $abortResult = $gateway->abort(request(), $transaction->prepare_data, $transaction->result_data);

        $this->updateTransactionAction->run($transaction, [
            'result_data' => $abortResult->persistentData,
            'status' => $abortResult->isSuccessful
                ? TransactionStatusEnum::Aborted
                : TransactionStatusEnum::AuthorizationFailed,
        ]);
    }

    /**
     * Handle the payment capture process.
     */
    protected function handlePaymentCapture(PaymentContract $gateway, Transaction $transaction): CaptureResult
    {
        $captureResult = $gateway->capture(request(), $transaction->prepare_data, $transaction->result_data);

        $this->updateTransactionAction->run($transaction, [
            'result_data' => $captureResult->persistentData,
            'status' => $captureResult->isSuccessful
                ? TransactionStatusEnum::Captured
                : TransactionStatusEnum::CaptureFailed,
        ]);

        return $captureResult;
    }

    /**
     * Handle the payment authorization process.
     */
    protected function handlePaymentAuthorization(PaymentContract $gateway, Transaction $transaction): AuthorizationResult
    {
        $authorizeResult = $gateway->authorize(request(), $transaction->prepare_data);

        $this->updateTransactionAction->run($transaction, [
            'result_data' => $authorizeResult->resultData,
            'status' => $authorizeResult->isSuccessful
                ? TransactionStatusEnum::Authorized
                : TransactionStatusEnum::AuthorizationFailed,
        ]);

        $transaction->refresh();

        return $authorizeResult;
    }

    /**
     * Return the stored output if the transaction already has result data.
     */
    protected function getOutput(Transaction $transaction): PaymentOutput
    {
        try {
            /** @phpstan-ignore-next-line */
            $data = collect($transaction->result_data['nezasa_booking_summary']['components'])
                ->reject(fn (array $item) => $item['isPlaceholder'])
                ->mapwithkeys(fn (array $item): array => [$item['id'] => AvailabilityEnum::tryFrom($item['status'])])
                ->toArray();
        } catch (\Throwable) {
            $data = [];
        }

        try {
            $bookingStatusEnum = $this->bookingResultAction->run($transaction->result_data['nezasa_booking_summary']);
        } catch (\Throwable) {
            $bookingStatusEnum = $transaction->checkout->rest_payment && $transaction->status->isCaptured()
                ? BookingStatusEnum::CompleteSuccess
                : BookingStatusEnum::Unknown;
        }

        return new PaymentOutput(
            gatewayName: $transaction->gateway,
            bookingStatusEnum: $bookingStatusEnum,
            bookingReference: $transaction->checkout->itinerary_id,
            orderDate: $transaction->updated_at?->toImmutable(),
            data: $data,
            isPaymentSuccessful: $transaction->status->isCaptured(),
        );
    }

    /**
     * Update the transaction with the booking summary.
     */
    protected function storeBookingSummary(Transaction $transaction, Response $bookingResponse): void
    {
        $this->updateTransactionAction->run($transaction->refresh(), [
            'result_data' => [
                ...$transaction->result_data,
                'nezasa_booking_summary' => $bookingResponse->array('summary'),
            ],
        ]);
    }
}
