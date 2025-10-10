<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Oppwa;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentInitiation;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

final class OppwaInitiationWidget implements WidgetPaymentInitiation
{
    /**
     * Prepare the payment before redirecting to the payment gateway.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        $response = OppwaConnector::make()->checkout()->prepare(
            payload: new OppwaPreparePayload(
                amount: $data->price->getPaymentAmount(),
                currency: $data->price->currency,
                customerEmail: $data->contact->email,
                customerGivenName: $data->contact->firstName,
                customerSurname: $data->contact->lastName,
                billingStreet1: $data->contact->address->street1,
                billingCity: $data->contact->address->city,
                billingPostcode: $data->contact->address->postalCode,
                billingCountry: str($data->contact->address->country)->before('-')->toString(),
            )
        );

        if ($response->ok()) {
            return new PaymentInit(
                isAvailable: true,
                persistentData: $response->dto(),
            );
        }

        return new PaymentInit(isAvailable: false);
    }

    /**
     * Get the assets (scripts, html) to render the payment form.
     *
     * @throws Exception
     */
    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset
    {
        if (! $paymentInit->persistentData instanceof OppwaPrepareResponse) {
            throw new Exception('The persistent data is not correct');
        }

        /** @var Collection<int, string> $scripts */
        $scripts = new Collection;

        $script = '<script
        src="https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId='.$paymentInit->persistentData->id.'"
        integrity="'.$paymentInit->persistentData->integrity.'"
        crossorigin="anonymous">
        </script>';

        $form = '<form action="'.$returnUrl.'" class="paymentWidgets" data-brands="VISA MASTER AMEX"> </form>';

        return new PaymentAsset(
            isAvailable: true,
            scripts: $scripts->add($script),
            html: $form
        );
    }

    /**
     * Get the Nezasa transaction payload for the payment.
     */
    public function getNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        if (! $paymentInit->persistentData instanceof OppwaPrepareResponse) {
            throw new Exception('The persistent data is not correct');
        }

        return new NezasaPayload(
            externalRefId: $paymentInit->persistentData->id,
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::CreditCard
        );
    }

    public static function name(): string
    {
        return Config::string('checkout.integrations.oppwa.name');
    }

    public static function description(): ?string
    {
        return null;
    }

    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.oppwa.active');
    }
}
