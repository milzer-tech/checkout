<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Support\Facades\URL;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Contracts\AddQueryParamsToReturnUrl;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentInitiation;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class WidgetInitiationHandler
{
    /**
     * Run the widget handler to prepare payment assets.
     */
    public function run(Checkout $model, PaymentPrepareData $data, string $gateway): PaymentAsset
    {
        $this->validateGateway($gateway);

        /** @var WidgetPaymentInitiation $payment */
        $payment = new $gateway;

        $init = $payment->prepare($data);

        $this->checkIfServiceAvailable($init);

        $nezasa = $this->createNezasaTransaction($data->checkoutId, $payment->getNezasaTransactionPayload($data, $init));

        $this->createTransaction($model, $init, $nezasa, $data->price, $payment::name());

        return $payment->getAssets(
            paymentInit: $init,
            returnUrl: URL::temporarySignedRoute(
                name: 'payment-result',
                expiration: now()->addMinutes(45),
                parameters: $this->getReturnUrlParams($data, $payment, $init)
            )
        );
    }

    /**
     * Get query parameters for the payment return URL.
     */
    private function validateGateway(string $gateway): void
    {
        if (! in_array(WidgetPaymentInitiation::class, (array) class_implements($gateway))) {
            throw new \InvalidArgumentException('The gateway does not implement PaymentInitiation.');
        }
    }

    /**
     * Create a transaction record for the payment.
     *
     * @param  array<string, string|array<string, mixed>>  $nezasaTransaction
     */
    private function createTransaction(Checkout $model, PaymentInit $init, array $nezasaTransaction, Price $price, string $gatewayName): void
    {
        $model->transactions()->create([
            'gateway' => $gatewayName,
            'prepare_data' => (array) $init->persistentData,
            'status' => PaymentStatusEnum::Pending,
            'nezasa_transaction' => $nezasaTransaction,
            'nezasa_transaction_ref_id' => $nezasaTransaction['transactionRefId'] ?? null,
            'amount' => $price->amount,
            'currency' => $price->currency,
        ]);
    }

    /**
     * Create the return URL parameters for the payment
     *
     * @return array<string, string> $params
     */
    private function getReturnUrlParams(PaymentPrepareData $data, WidgetPaymentInitiation $payment, PaymentInit $init): array
    {
        return array_merge(
            [
                'checkoutId' => $data->checkoutId,
                'itineraryId' => $data->itineraryId,
                'origin' => $data->origin,
                'lang' => $data->lang,
            ],
            $payment instanceof AddQueryParamsToReturnUrl ? $payment->addQueryParamsToReturnUrl($init) : []
        );
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
}
