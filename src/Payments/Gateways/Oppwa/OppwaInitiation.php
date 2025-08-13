<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Oppwa;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Contracts\PaymentInitiation;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;

final class OppwaInitiation implements PaymentInitiation
{
    public function prepare(Checkout $checkout, Price $price): PaymentInit
    {
        $contact = ContactInfoPayloadEntity::from($checkout->data['contact']);

        $response = OppwaConnector::make()->checkout()->prepare(
            payload: new OppwaPreparePayload(
                amount: $price->getPaymentAmount(),
                currency: $price->currency,
                customerEmail: $contact->email,
                customerGivenName: $contact->firstName,
                customerSurname: $contact->lastName,
                billingStreet1: $contact->address->street1,
                billingCity: $contact->address->city,
                billingPostcode: $contact->address->postalCode,
                billingCountry: str($contact->address->country)->before('-')->toString(),
            )
        );

        if ($response->ok()) {
            return new PaymentInit(
                gateway: PaymentGatewayEnum::Oppwa,
                isAvailable: true,
                data: $response->dto(),
                persistentData: $response->dto(),
            );
        }

        return new PaymentInit(gateway: PaymentGatewayEnum::Oppwa, isAvailable: false);
    }

    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset
    {
        $script = '<script
        src="https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId='.$paymentInit->data->id.'"
        integrity="'.$paymentInit->data->integrity.'"
        crossorigin="anonymous">
        </script>';

        $form = '<form action="'.$returnUrl.'" class="paymentWidgets" data-brands="VISA MASTER AMEX"> </form>';

        return new PaymentAsset(
            gateway: PaymentGatewayEnum::Oppwa,
            scripts: new Collection([$script]),
            html: $form,
            isAvailable: true
        );
    }
}
