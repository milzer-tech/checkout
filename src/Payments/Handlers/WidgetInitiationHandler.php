<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Support\Facades\URL;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Contracts\AddQueryParamsToReturnUrl;
use Nezasa\Checkout\Payments\Contracts\PaymentInitiation;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaInitiation;

class WidgetInitiationHandler
{
    /**
     * Implementations of payment gateways.
     *
     * @var array<int, class-string<PaymentInitiation>>
     */
    private array $implementations = [
        PaymentGatewayEnum::Oppwa->value => OppwaInitiation::class,
    ];

    /**
     * Run the widget handler to prepare payment assets.
     */
    public function run(Checkout $model, PaymentPrepareData $data, PaymentGatewayEnum $gateway): PaymentAsset
    {
        $this->validateGateway($gateway);

        /** @var PaymentInitiation $payment */
        $payment = new $this->implementations[$gateway->value];

        $init = $payment->prepare($data);

        $this->checkIfServiceAvailable($init);

        $nezasa = $this->createNezasaTransaction($data->checkoutId, $payment->getNezasaTransactionPayload($data, $init));

        $this->createTransaction($model, $init, $nezasa, $data->price);

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
    private function validateGateway(PaymentGatewayEnum $gateway): void
    {
        if (! array_key_exists($gateway->value, $this->implementations)) {
            throw new \InvalidArgumentException('The payment gateway is not supported.');
        }

        if (! in_array(PaymentInitiation::class, class_implements($this->implementations[$gateway->value]))) {
            throw new \InvalidArgumentException('The gateway does not implement PaymentInitiation.');
        }
    }

    /**
     * Create a transaction record for the payment.
     */
    private function createTransaction(Checkout $model, PaymentInit $init, array $nezasaTransaction, Price $price): void
    {
        $model->transactions()->create([
            'gateway' => $init->gateway,
            'prepare_data' => (array) $init->persistentData,
            'status' => PaymentStatusEnum::Pending,
            'nezasa_transaction' => $nezasaTransaction,
            'nezasa_transaction_ref_id' => $nezasaTransaction['transactionRefId'] ?? null,
            'amount' => $price->amount,
            'currency' => $price->currency,
        ]);
    }

    /**
     * Create the return URL parameters for the payment.
     */
    private function getReturnUrlParams(PaymentPrepareData $data, PaymentInitiation $payment, PaymentInit $init): array
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

    private function createNezasaTransaction(string $checkoutId, CreatePaymentTransactionPayload $payload): array
    {
        return NezasaConnector::make()
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
