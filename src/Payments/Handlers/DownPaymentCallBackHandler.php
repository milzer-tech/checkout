<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Http\Request;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;

readonly class DownPaymentCallBackHandler extends PaymentCallBackHandler
{
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
                $captureResult = $this->handlePaymentCapture($gateway, $transaction);

                if ($captureResult->isSuccessful) {
                    event(new ItineraryBookingSucceededEvent($transaction));
                }
            }

            $this->storeBookingSummary($transaction, $bookingResponse);
        }
        // Nezasa API does not support other statuses.
        $this->closeNezasaTransactionAction->run(NezasaTransactionStatusEnum::Closed, $transaction);

        return $this->getOutput($transaction);
    }
}
