<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Computop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Computop\Connectors\ComputopConnector;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopPaymentPayload;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\ComputopAmountDto;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\OrderPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\UrlPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

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
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        try {
            $payload = new ComputopPaymentPayload(
                transactionId: (string) $data->transaction->id,
                amount: new ComputopAmountDto($data->price->toCent(), $data->price->currency),
                order: new OrderPayloadEntity(id: $data->checkoutId, description: ['The itinerary price']),
                urls: new UrlPayloadEntity(
                    success: (string) $data->returnUrl,
                    failure: ((string) $data->returnUrl).'&failure=1',
                    cancel: route('traveler-details', [
                        'checkoutId' => $data->checkoutId,
                        'itineraryId' => $data->itineraryId,
                        'origin' => $data->origin,
                        'lang' => $data->lang,
                    ]),
                ),
                language: $data->lang,
            );

            $response = ComputopConnector::make()->payment()->init($payload);

            if (! $response->failed()) {
                return new PaymentInit(
                    isAvailable: true,
                    returnUrl: $data->returnUrl,
                    persistentData: ['response' => $response->array(), 'amount' => $payload->amount->toArray()]
                );
            }
        } catch (\Throwable $e) {

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
            paymentMethodName: self::name()
        );
    }

    /**
     * Handles the callback from the payment gateway.
     *
     * @param  array<string, mixed>|BaseDto  $persistentData
     */
    public function verify(Request $request, BaseDto|array $persistentData): PaymentResult
    {
        try {
            $response = ComputopConnector::make()->payment()->get($request->query('PayID'));

            $amount = ComputopAmountDto::from($persistentData['amount'])->value;

            $approved = (int) $response->array('amount.approvedValue');
            $captured = (int) $response->array('amount.capturedValue');
            $credited = (int) $response->array('amount.creditedValue');

            if ($response->ok()
                && $response->array('status') === 'OK'
                && $approved === $amount
                && $captured === $amount
                && $credited === $amount
            ) {
                return new PaymentResult(status: PaymentStatusEnum::Succeeded, persistentData: $response->array());
            }
        } catch (\Throwable $e) {
            // do nothing
        }

        return new PaymentResult(status: PaymentStatusEnum::Failed, persistentData: $request->query());
    }

    /**
     * Shows the result of the payment process to the user.
     */
    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}
