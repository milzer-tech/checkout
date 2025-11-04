<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Invoice;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Payments\Contracts\AddQueryParamsToReturnUrl;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentInitiation;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

final class InvoiceInitiationWidget implements AddQueryParamsToReturnUrl, WidgetPaymentInitiation
{
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.invoice.active');
    }

    public static function name(): string
    {
        return Config::string('checkout.integrations.invoice.name');
    }

    public static function description(): ?string
    {
        return null;
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        return new PaymentInit(
            isAvailable: true,
            persistentData: [
                'id' => Str::ulid()->toString(),
            ]
        );

    }

    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset
    {
        return new PaymentAsset(isAvailable: true, scripts: ['url' => $returnUrl]);
    }

    public function getNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            /** @phpstan-ignore-next-line  */
            externalRefId: $paymentInit->persistentData['id'],
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::Invoice
        );
    }

    public function addQueryParamsToReturnUrl(PaymentInit $paymentInit): array
    {
        return [
            /** @phpstan-ignore-next-line  */
            'key' => $paymentInit->persistentData['id'],
        ];
    }
}
