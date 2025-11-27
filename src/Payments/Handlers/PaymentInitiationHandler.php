<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Exception;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class PaymentInitiationHandler
{
    /**
     * Run the widget handler to prepare payment assets.
     */
    public function run(Checkout $model, Price $price, PaymentContract $gateway): Uri|PaymentAsset
    {
        $transaction = $this->createTransaction($model, $price, $gateway);

        $prepareData = $this->makePaymentPrepareData($transaction);

        $init = $gateway->prepare($prepareData);

        $this->checkIfServiceAvailable($init);

        $nezasa = $this->createNezasaTransaction(
            $model->checkout_id,
            $gateway->makeNezasaTransactionPayload($prepareData, $init)
        );

        $this->updateTransaction($transaction, $init, $nezasa);

        if ($gateway instanceof WidgetPaymentContract) {
            return $gateway->getAssets(paymentInit: $init);
        }

        if ($gateway instanceof RedirectPaymentContract) {
            return $gateway->getRedirectUrl($init);
        }

        throw new Exception('Payment gateway is not supported.');
    }

    /**
     * Create a transaction record for the payment.
     */
    private function createTransaction(Checkout $checkout, Price $price, PaymentContract $gateway): Transaction
    {
        return $checkout->transactions()->create([
            'gateway' => $gateway::name(),
            'status' => PaymentStatusEnum::Started,
            'amount' => $price->amount,
            'currency' => $price->currency,
        ]);
    }

    /**
     * Update the transaction record for the payment.
     */
    private function updateTransaction(Transaction $transaction, PaymentInit $init, array $nezasaTransaction): void
    {
        $transaction->update([
            'prepare_data' => (array) $init->persistentData,
            'status' => PaymentStatusEnum::Pending,
            'nezasa_transaction' => $nezasaTransaction,
            'nezasa_transaction_ref_id' => $nezasaTransaction['transactionRefId'] ?? null,
        ]);
    }

    /**
     * Create a payment transaction in Nezasa.
     *
     * @return array<string, string|array<string, mixed>>
     */
    private function createNezasaTransaction(string $checkoutId, CreatePaymentTransactionPayload $payload): array
    {
        return (array) NezasaConnector::make()
            ->paymentTransaction()
            ->create($checkoutId, $payload)
            ->array('transaction');
    }

    /**
     * Check if the payment service is available.
     */
    private function checkIfServiceAvailable(PaymentInit $init): void
    {
        if (! $init->isAvailable) {
            throw new \RuntimeException('Payment gateway is not available.');
        }
    }

    /**
     * Make the payment prepare data.
     */
    public function makePaymentPrepareData(Transaction $transaction): PaymentPrepareData
    {
        return new PaymentPrepareData(
            returnUrl: Uri::route('payment-result', [
                'transaction' => $transaction,
                'checkoutId' => $transaction->checkout->checkout_id,
                'itineraryId' => $transaction->checkout->itinerary_id,
                'origin' => request()->input('origin'),
                'lang' => request()->input('lang', 'en'),
            ]),
            contact: ContactInfoPayloadEntity::from($transaction->checkout->data['contact']),
            price: new Price((float) $transaction->amount, $transaction->currency),
            checkoutId: $transaction->checkout->checkout_id,
            itineraryId: $transaction->checkout->itinerary_id,
            origin: request()->input('origin'),
            lang: request()->input('lang', 'en'),
        );
    }
}
