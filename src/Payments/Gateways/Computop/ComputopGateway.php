<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Computop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Computop\Connectors\ComputopConnector;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopCapturePaymentPayload;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopPaymentPayload;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\CaptureInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\CaptureManualPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\ComputopAmountDto;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\OrderPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\UrlPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

class ComputopGateway implements RedirectPaymentContract
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.computop.active');
    }

    /**
     * Returns the name of the payment gateway.
     *
     * Important: This name will be used to identify the payment gateway in the checkout process
     * and it has to be unique, please check the previous gateways' names,
     */
    public static function name(): string
    {
        return Config::string('checkout.integrations.computop.name');
    }

    /**
     * Prepares the payment initiation process.
     * // 5232125125401459
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        try {
            $orderDescription = Config::boolean('checkout.integrations.computop.test_mode')
                ? ['Test:0000']
                : ['The itinerary price'];

            $payload = new ComputopPaymentPayload(
                transactionId: (string) $data->transaction->id,
                amount: new ComputopAmountDto($data->price->toCent(), $data->price->currency),
                order: new OrderPayloadEntity(id: $data->checkoutId, description: $orderDescription),
                urls: new UrlPayloadEntity(
                    success: (string) $data->returnUrl,
                    failure: ($data->returnUrl).'&failure=1',
                    cancel: (string) $data->cancelUrl,
                ),
                capture: new CaptureInfoPayloadEntity(
                    manual: new CaptureManualPayloadEntity(final: 'yes')
                ),
                language: $data->lang,
            );

            $response = ComputopConnector::make()->payment()->init($payload);

            if (! $response->failed()) {
                return new PaymentInit(
                    isAvailable: true,
                    returnUrl: $data->returnUrl,
                    persistentData: [
                        'response' => $response->array(),
                        'amount' => $payload->amount->toArray(),
                        'paylaod' => $payload->toArray(),
                    ]
                );
            }
        } catch (\Throwable) {

        }

        return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
    }

    /**
     * The url to the payment gateway.
     */
    public function getRedirectUrl(PaymentInit $init): Uri
    {
        return Uri::of(
            data_get($init->persistentData, 'response._Links.redirect.href')
        );
    }

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            /** @phpstan-ignore-next-line */
            externalRefId: $paymentInit->persistentData['response']['paymentId'],
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::Other,
            paymentMethodName: 'Computop'
        );
    }

    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        try {
            $response = ComputopConnector::make()->payment()->get($request->query('PayID'));

            return new AuthorizationResult(
                isSuccessful: $response->ok() && in_array($response->array('status'), ['CAPTURE_REQUEST', 'OK']),
                resultData: ['payment' => $response->array()]
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return new AuthorizationResult(isSuccessful: false, resultData: (array) $request->query());
    }

    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        try {
            $payload = new ComputopCapturePaymentPayload(
                transactionId: (string) $resultData['payment']['transactionId'],
                amount: ComputopAmountDto::from($persistentData['amount'])
            );

            $response = ComputopConnector::make()->payment()->capture($request->query('PayID'), $payload);
            $resultData['capture'] = $response->array();

            return new CaptureResult(
                isSuccessful: $response->ok() && in_array($response->array('status'), ['CAPTURE_REQUEST', 'OK']),
                persistentData: $resultData
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return new CaptureResult(isSuccessful: false, persistentData: $resultData);
    }

    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        return new AbortResult(isSuccessful: true, persistentData: $resultData);
    }
}
