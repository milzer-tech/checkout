<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Oppwa;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Throwable;

class OppwaWidgetGateway implements WidgetPaymentContract
{
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.oppwa.active');
    }

    public static function name(): string
    {
        return Config::string('checkout.integrations.oppwa.name');
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        try {
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
                return new PaymentInit(isAvailable: true, returnUrl: $data->returnUrl, persistentData: $response->dto());
            }
        } catch (Throwable) {
            // nothing to do
        }

        return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
    }

    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
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

    public function getAssets(PaymentInit $paymentInit): PaymentAsset
    {
        if (! $paymentInit->persistentData instanceof OppwaPrepareResponse) {
            throw new Exception('The persistent data is not correct');
        }

        $returnUrl = $paymentInit->returnUrl->toStringable()->toString();

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
            /** @phpstan-ignore-next-line  */
            scripts: $scripts->add($script),
            html: $form
        );
    }

    public function verify(Request $request, BaseDto|array $persistentData): PaymentResult
    {
        try {
            $response = OppwaConnector::make()->checkout()->status($request->query('resourcePath'));

            return new PaymentResult(
                status: $response->failed() ? PaymentStatusEnum::Failed : PaymentStatusEnum::Succeeded,
                persistentData: (array) $response->array(),
            );
        } catch (Throwable $exception) {
            report($exception);

            return new PaymentResult(status: PaymentStatusEnum::Failed);
        }
    }

    /**
     * Show the payment result to the user.
     */
    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}
