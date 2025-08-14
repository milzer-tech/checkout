<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Support\Facades\URL;
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
    public function run(Checkout $model, PaymentPrepareData $prepareData, PaymentGatewayEnum $gateway): PaymentAsset
    {
        $this->validateGateway($gateway);

        $payment = new $this->implementations[$gateway->value];

        $init = $payment->prepare($prepareData);

        $this->createTransaction($model, $init);

        return $payment->getAssets(
            paymentInit: $init,
            returnUrl: URL::temporarySignedRoute(
                name: 'payment-result',
                expiration: now()->addMinutes(45),
                parameters: $this->getReturnUrlParams($prepareData, $payment, $init)
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
    private function createTransaction(Checkout $model, PaymentInit $init): void
    {
        $model->transactions()->create([
            'gateway' => $init->gateway,
            'prepare_data' => (array) $init->persistentData,
            'status' => PaymentStatusEnum::Pending,
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
}
