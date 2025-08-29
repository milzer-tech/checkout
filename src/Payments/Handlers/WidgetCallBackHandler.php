<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\UpdatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\BookingStateEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentCallBack;
use Nezasa\Checkout\Payments\Contracts\ReturnUrlHasInvalidQueryParamsForValidation;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaCallBack;
use Throwable;

class WidgetCallBackHandler
{
    /**
     * Implementations of payment gateways.
     *
     * @var array<int, class-string<PaymentCallBack>>
     */
    private array $implementations = [
        PaymentGatewayEnum::Oppwa->value => OppwaCallBack::class,
    ];

    /**
     * Handle the payment callback process.
     */
    public function run(Transaction $transaction, Request $request): PaymentOutput
    {
        $this->validateGateway($transaction->gateway);

        /** @var PaymentCallBack $callback */
        $callback = new $this->implementations[$transaction->gateway->value];

        if ($transaction->result_data) {
            return $this->getOutput($transaction, $callback);
        }

        $this->validateReturnUrl($callback, $request);

        $result = $callback->check(request(), (array) $transaction->prepare_data);

        $nezasaTransaction = $this->updateNezasaTransaction($result->status, $transaction);

        $this->storeResult($result, $transaction, $nezasaTransaction);

        $this->bookItinerary($transaction->checkout);

        return $this->getOutput($transaction, $callback);
    }

    /**
     * Validate if the payment gateway is supported and implemented correctly.
     */
    private function validateGateway(PaymentGatewayEnum $gateway): void
    {
        if (! array_key_exists($gateway->value, $this->implementations)) {
            throw new \InvalidArgumentException('The payment gateway is not supported.');
        }

        if (! in_array(PaymentCallBack::class, class_implements($this->implementations[$gateway->value]))) {
            throw new \InvalidArgumentException('The payment callback is not implemented correctly.');
        }
    }

    /**
     * Validate the return URL signature.
     */
    private function validateReturnUrl(mixed $callback, Request $request): void
    {
        $ignoreQuery = $callback instanceof ReturnUrlHasInvalidQueryParamsForValidation
            ? $callback->addedParamsToReturnedUrl($request)
            : [];

        if (! $request->hasValidSignatureWhileIgnoring($ignoreQuery)) {
            abort(403, 'Invalid signature');
        }
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
    private function getOutput(Transaction $transaction, PaymentCallBack $callback): PaymentOutput
    {
        $response = NezasaConnector::make()->checkout()->retrieve($transaction->checkout->checkout_id);

        Log::info('checkout response', $response->json());

        /** @var BookingStateEnum $state */
        $state = $response->dto()->checkoutState;

        $result = new PaymentResult(
            gateway: $transaction->gateway,
            status: $transaction->status,
            persistentData: $transaction->result_data,
        );

        $output = new PaymentOutput(
            gateway: $result->gateway,
            isNezasaBookingSuccessful: $state->isSuccessfulState(),
            bookingReference: $transaction->checkout->itinerary_id,
            orderDate: $transaction->updated_at->toImmutable(),
            data: $result->persistentData
        );

        return $callback->show($result, $output);
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
