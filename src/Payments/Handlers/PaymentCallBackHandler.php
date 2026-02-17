<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Http\Request;
use Nezasa\Checkout\Actions\Checkout\BookItineraryAction;
use Nezasa\Checkout\Actions\Checkout\FindBookingResultAction;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Payment\UpdateNezasaTransactionAction;
use Nezasa\Checkout\Actions\Transaction\UpdateTransactionAction;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Saloon\Http\Response;

readonly class PaymentCallBackHandler
{
    /**
     * Create a new instance of PaymentCallBackHandler.
     */
    public function __construct(
        private BookItineraryAction $bookItineraryAction,
        private UpdateTransactionAction $updateTransactionAction,
        private UpdateNezasaTransactionAction $updateNezasaTransactionAction,
        private FindBookingResultAction $bookingResultAction,
    ) {}

    /**
     * Handle the payment callback process.
     */
    public function run(Transaction $transaction, Request $request): PaymentOutput
    {
        $gateway = $this->getCallBackClass($transaction->gateway);

        if ($transaction->result_data) {
            return $this->getOutput($transaction);
        }

        if ($this->handlePaymentAuthorization($gateway, $transaction)->isSuccessful) {
            $bookingResponse = $this->bookItineraryAction->run($transaction->checkout->checkout_id);
            $bookingResult = $this->bookingResultAction->run($bookingResponse->array('summary'));

            if ($bookingResult->isCompleteFailed() || $bookingResult->isUnknown()) {
                $this->handlePaymentAbort($gateway, $transaction);
            }

            if ($bookingResult->isCompleteSuccess() || $bookingResult->isPartialFailure()) {
                $this->handlePaymentCapture($gateway, $transaction);
            }

            $this->storeBookingSummary($transaction, $bookingResponse);
        }
        // Nezasa API does not support other statuses.
        $this->updateNezasaTransactionAction->run(NezasaTransactionStatusEnum::Closed, $transaction);

        return $this->getOutput($transaction);
    }

    /**
     * Validate if the payment gateway is supported and implemented correctly.
     */
    private function getCallBackClass(string $gateway): PaymentContract
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
    private function handlePaymentAbort(PaymentContract $gateway, Transaction $transaction): void
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
    private function handlePaymentCapture(PaymentContract $gateway, Transaction $transaction): void
    {
        $captureResult = $gateway->capture(request(), $transaction->prepare_data, $transaction->result_data);

        $this->updateTransactionAction->run($transaction, [
            'result_data' => $captureResult->persistentData,
            'status' => $captureResult->isSuccessful
                ? TransactionStatusEnum::Captured
                : TransactionStatusEnum::CaptureFailed,
        ]);

        if ($captureResult->isSuccessful) {
            event(new ItineraryBookingSucceededEvent($transaction));
        }
    }

    /**
     * Handle the payment authorization process.
     */
    private function handlePaymentAuthorization(PaymentContract $gateway, Transaction $transaction): AuthorizationResult
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
    private function getOutput(Transaction $transaction): PaymentOutput
    {
        try {
            /** @phpstan-ignore-next-line */
            $data = collect($transaction->result_data['nezasa_booking_summary']['components'])
                ->reject(fn (array $item) => $item['isPlaceholder'])
                ->mapwithkeys(fn (array $item): array => [$item['id'] => AvailabilityEnum::tryFrom($item['status'])])
                ->toArray();
        } catch (\Throwable $th) {
            $data = [];
        }

        return new PaymentOutput(
            gatewayName: $transaction->gateway,
            bookingStatusEnum: $this->bookingResultAction->run($transaction->result_data['nezasa_booking_summary']),
            bookingReference: $transaction->checkout->itinerary_id,
            orderDate: $transaction->updated_at?->toImmutable(),
            data: $data,
            isPaymentSuccessful: $transaction->status->isCaptured(),
        );
    }

    /**
     * Update the transaction with the booking summary.
     */
    private function storeBookingSummary(Transaction $transaction, Response $bookingResponse): void
    {
        $this->updateTransactionAction->run($transaction->refresh(), [
            'result_data' => [
                ...$transaction->result_data,
                'nezasa_booking_summary' => $bookingResponse->array('summary'),
            ],
        ]);
    }
}
