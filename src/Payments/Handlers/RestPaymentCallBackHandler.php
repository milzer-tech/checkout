<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Http\Request;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;

readonly class RestPaymentCallBackHandler extends PaymentCallBackHandler
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

        $this->handlePaymentAuthorization($gateway, $transaction)->isSuccessful
            ? $this->handlePaymentCapture($gateway, $transaction)
            : $this->deleteNezasaTransactionAction->run($transaction);

        return $this->getOutput($transaction);
    }
}
