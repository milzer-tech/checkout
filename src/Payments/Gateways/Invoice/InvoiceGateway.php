<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Invoice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class InvoiceGateway implements RedirectPaymentContract
{
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.invoice.active');
    }

    public static function name(): string
    {
        return Config::string('checkout.integrations.invoice.name');
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        return new PaymentInit(
            isAvailable: true,
            returnUrl: $data->returnUrl,
            persistentData: [
                'id' => Str::ulid()->toString(),
            ]
        );
    }

    public function getRedirectUrl(PaymentInit $init): Uri
    {
        return $init->returnUrl;
    }

    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            /** @phpstan-ignore-next-line  */
            externalRefId: $paymentInit->persistentData['id'],
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::Invoice
        );
    }

    public function verify(Request $request, BaseDto|array $persistentData): PaymentResult
    {
        $id = is_array($persistentData) ? $persistentData['id'] : false;

        return new PaymentResult(
            status: $request->input('key') === $id
                ? PaymentStatusEnum::Succeeded
                : PaymentStatusEnum::Failed,
            persistentData: (array) $persistentData
        );
    }

    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}
