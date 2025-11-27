<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\UpdatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\BookingStateEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Throwable;

class PaymentCallBackHandler
{
    /**
     * Handle the payment callback process.
     */
    public function run(Transaction $transaction, Request $request): PaymentOutput
    {
        $gateway = $this->getCallBackClass($transaction->gateway);

        // Means the payment was already completed.
        if ($transaction->result_data) {
            return $this->getOutput($transaction, $gateway);
        }

        $result = $gateway->verify(request(), (array) $transaction->prepare_data);

        $nezasaTransaction = $this->updateNezasaTransaction($result->status, $transaction);

        $this->storeResult($result, $transaction, $nezasaTransaction);

        $this->bookItinerary($transaction->checkout);

        return $this->getOutput($transaction, $gateway);
    }

    /**
     * Validate if the payment gateway is supported and implemented correctly.
     */
    private function getCallBackClass(string $gateway): PaymentContract
    {   /** @var class-string<PaymentContract> */
        $result = collect(resolve(GetPaymentProviderAction::class)->run())
            ->where('name', $gateway)
            ->firstOrFail()
            ->decryptClassName();

        return new $result;
    }

    /**
     * Store the result of the payment callback in the transaction.
     *
     * @param  false|array<string, mixed>  $nezasaTransaction
     */
    private function storeResult(PaymentResult $result, Transaction $model, false|array $nezasaTransaction): void
    {
        $model->update([
            'result_data' => $result->persistentData,
            'status' => $result->status->value,
            'nezasa_transaction' => $nezasaTransaction ?: $model->nezasa_transaction,
        ]);
    }

    /**
     * Update the Nezasa transaction with the payment status.
     *
     * @return false|array<string, mixed>
     */
    private function updateNezasaTransaction(PaymentStatusEnum $status, Transaction $transaction): false|array
    {
        try {
            $payload = new UpdatePaymentTransactionPayload(
                status: $status->isSucceeded()
                    ? NezasaTransactionStatusEnum::Closed
                    : NezasaTransactionStatusEnum::Failed
            );

            return NezasaConnector::make()
                ->paymentTransaction()
                ->update($transaction->checkout->checkout_id, $transaction->nezasa_transaction_ref_id, $payload)
                ->array('transaction');
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /**
     * Return the stored output if the transaction already has result data.
     */
    private function getOutput(Transaction $transaction, PaymentContract $callback): PaymentOutput
    {
        $response = NezasaConnector::make()->checkout()->retrieve($transaction->checkout->checkout_id);

        Log::info('checkout response', $response->json());

        /** @var BookingStateEnum $state */
        $state = $response->dto()->checkoutState;

        $result = new PaymentResult(
            status: $transaction->status ?? PaymentStatusEnum::Failed,
            persistentData: $transaction->result_data ?? [],
        );

        $output = new PaymentOutput(
            gatewayName: $transaction->gateway,
            isNezasaBookingSuccessful: $state->isSuccessfulState(),
            bookingReference: $transaction->checkout->itinerary_id,
            orderDate: $transaction->updated_at?->toImmutable(),
            data: $result->persistentData
        );

        return $callback->output($result, $output);
    }

    /**
     * Attempt to book the itinerary if the payment was successful.
     */
    private function bookItinerary(Checkout $checkout): bool
    {
        try {
            $response = NezasaConnector::make()->checkout()->synchronousBooking($checkout->checkout_id);

            Log::info('booking response', $response->json());

            return $response->ok();
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
