<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Invoice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

class InvoiceGateway implements RedirectPaymentContract
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.invoice.active');
    }

    /**
     * Returns the name of the payment gateway.
     *
     * Important: This name will be used to identify the payment gateway in the checkout process
     * and it has to be unique, please check the previous gateways' names,
     */
    public static function name(): string
    {
        return Config::string('checkout.integrations.invoice.name');
    }

    /**
     * Prepares the payment initiation process.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        return new PaymentInit(
            isAvailable: true,
            returnUrl: $data->returnUrl,
            persistentData: [
                'id' => $data->transaction->id,
            ]
        );
    }

    /**
     * The url to the payment gateway.
     */
    public function getRedirectUrl(PaymentInit $init): Uri
    {
        return $init->returnUrl;
    }

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            /** @phpstan-ignore-next-line  */
            externalRefId: $paymentInit->persistentData['id'],
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::BankTransfer
        );
    }

    /**
     * Handles the callback from the payment gateway.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        return new AuthorizationResult(
            /** @phpstan-ignore-next-line */
            isSuccessful: $request->route('transaction')->id === $persistentData['id'],
            resultData: $persistentData
        );
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
        return new CaptureResult(isSuccessful: true, persistentData: $resultData);
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
        return new AbortResult(isSuccessful: true, persistentData: $resultData);
    }
}
