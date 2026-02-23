<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Oppwa;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaComplationPayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Throwable;

class OppwaWidgetGateway implements WidgetPaymentContract
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.oppwa.active');
    }

    /**
     * Returns the name of the payment gateway.
     *
     * Important: This name will be used to identify the payment gateway in the checkout process
     * and it has to be unique, please check the previous gateways' names,
     */
    public static function name(): string
    {
        return Config::string('checkout.integrations.oppwa.name');
    }

    /**
     * Prepares the payment initiation process.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        try {
            $payload = new OppwaPreparePayload(
                amount: $data->price->getPaymentAmount(),
                currency: $data->price->currency,
                customerEmail: $data->contact->email,
                paymentType: 'PA'
            );

            $response = OppwaConnector::make()->checkout()->prepare(payload: $payload);

            if ($response->ok()) {
                return new PaymentInit(
                    isAvailable: true,
                    returnUrl: $data->returnUrl,
                    persistentData: [
                        'prepare' => $response->dto(),
                        'prepare_payload' => $payload,
                        'contact' => $data->contact,
                    ]
                );
            }
        } catch (Throwable $throwable) {
            report($throwable);
        }

        return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
    }

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        if (! isset($paymentInit->persistentData['prepare']) || ! $paymentInit->persistentData['prepare'] instanceof OppwaPrepareResponse) {
            throw new Exception('The persistent data is not correct');
        }

        return new NezasaPayload(
            externalRefId: $paymentInit->persistentData['prepare']->id,
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::Other,
            paymentMethodName: 'Oppwa'
        );
    }

    /**
     * Returns the assets required for the payment initiation process.
     */
    public function getAssets(PaymentInit $paymentInit): PaymentAsset
    {
        if (! isset($paymentInit->persistentData['prepare']) || ! $paymentInit->persistentData['prepare'] instanceof OppwaPrepareResponse) {
            throw new Exception('The persistent data is not correct');
        }

        /** @var ContactInfoPayloadEntity $contact */
        $contact = $paymentInit->persistentData['contact'];
        $returnUrl = $paymentInit->returnUrl->toStringable()->toString();

        /** @var Collection<int, string> $scripts */
        $scripts = collect([
            '<script>
                    var wpwlOptions = {
                        forceCardHolderEqualsBillingName: true,
                            billingAddress: {
                                country: "'.$contact->address->getCountryCode().'",
                                city: "'.$contact->address->city.'",
                                postcode: "'.$contact->address->getNumericPostalCode().'",
                                street1: "'.$contact->address->street1.'"
                            },
                            mandatoryBillingFields:{
                                country: true,
                                state: false,
                                city: false,
                                postcode: false,
                                street1: false,
                                street2: false
                            }
                        }
            </script>',
            '<script
                src="https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId='.$paymentInit->persistentData['prepare']->id.'"
                integrity="'.$paymentInit->persistentData['prepare']->integrity.'"
                crossorigin="anonymous">
            </script>',
        ]);

        $form = '<form action="'.$returnUrl.'" class="paymentWidgets" data-brands="VISA MASTER AMEX"> </form>';

        return new PaymentAsset(isAvailable: true, scripts: $scripts, html: $form);
    }

    /**
     * Handles the callback from the payment gateway to authorize the payment.
     *
     * Persistent data is the data that is returned from paymentInit in the prepare method.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        try {
            $response = OppwaConnector::make()->checkout()->status($request->query('resourcePath'));

            return new AuthorizationResult(
                isSuccessful: ! $response->failed(),
                resultData: ['status' => (array) $response->array()]
            );
        } catch (Throwable $exception) {
            report($exception);

            return new AuthorizationResult(isSuccessful: false);
        }
    }

    /**
     * Capture the authorized payment. This method is called after the payment is authorized
     * and booking itinerary call is successful.
     *
     * Persistent data is the data returned from paymentInit in the prepare method.
     *
     * @param  array<string, mixed>  $persistentData
     *
     * Result data is the data returned from AuthorizationResult's resultData property.
     * @param  array<string, mixed>  $resultData
     */
    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        try {
            $response = OppwaConnector::make()->checkout()->complete(
                id: $resultData['status']['id'],
                payload: new OppwaComplationPayload(
                    amount: $resultData['status']['amount'],
                    currency: $resultData['status']['currency'],
                    paymentType: 'CP'
                )
            );

            $resultData['capture'] = $response->array();

            return new CaptureResult(isSuccessful: ! $response->failed(), persistentData: $resultData);

        } catch (Throwable $exception) {
            report($exception);

            return new CaptureResult(isSuccessful: false);
        }
    }

    /**
     * Abort the payment process. This method is called when the booking itinerary call fails.
     *
     * Persistent data is the data returned from paymentInit in the prepare method.
     *
     * @param  array<string, mixed>  $persistentData
     *
     * Result data is the data returned from AuthorizationResult's resultData property.
     * @param  array<string, mixed>  $resultData
     */
    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        try {
            $response = OppwaConnector::make()->checkout()->complete(
                id: $resultData['status']['id'],
                payload: new OppwaComplationPayload(
                    amount: $resultData['status']['amount'],
                    currency: $resultData['status']['currency'],
                    paymentType: 'RV'
                )
            );

            $resultData['abort'] = $response->array();

            return new AbortResult(isSuccessful: ! $response->failed(), persistentData: $resultData);

        } catch (Throwable $exception) {
            report($exception);

            return new AbortResult(isSuccessful: false);
        }
    }
}
