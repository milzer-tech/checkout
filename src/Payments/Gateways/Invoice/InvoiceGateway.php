<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Invoice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

class InvoiceGateway
// implements RedirectPaymentContract
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
     * @param  array<string, mixed>|BaseDto  $persistentData
     */
    public function verify(Request $request, BaseDto|array $persistentData): AuthorizationResult
    {
        $id = is_array($persistentData) ? $persistentData['id'] : false;

        return new AuthorizationResult(
            /** @phpstan-ignore-next-line  */
            //            status: $request->route('transaction')->id === $id
            //                ? TransactionStatusEnum::Succeeded
            //                : TransactionStatusEnum::Failed,
            isSuccessful: true,
            resultData: (array) $persistentData
        );
    }

    /**
     * Shows the result of the payment process to the user.
     */
    public function output(AuthorizationResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}
