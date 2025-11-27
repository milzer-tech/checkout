<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Stripe;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
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
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class StripeGateway implements RedirectPaymentContract
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool
    {
        return Config::get('checkout.integrations.stripe.active');
    }

    /**
     * Returns the name of the payment gateway.
     *
     * Important: This name will be used to identify the payment gateway in the checkout process
     * and it has to be unique, please check the previous gateways' names,
     */
    public static function name(): string
    {
        return Config::get('checkout.integrations.stripe.name');
    }

    /**
     * Prepares the payment initiation process.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        try {
            Stripe::setApiKey(Config::string('checkout.integrations.stripe.stripeSecretKey'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $data->price->currency,
                            'product_data' => [
                                'name' => 'The itinerary price',
                            ],
                            'unit_amount' => $data->price->toCent(), // amount in cents
                        ],
                        'quantity' => 1,
                    ],
                ],
                'success_url' => $data->returnUrl.'&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('traveler-details', [
                    'checkoutId' => $data->checkoutId,
                    'itineraryId' => $data->itineraryId,
                    'origin' => $data->origin,
                    'lang' => $data->lang,
                ]),

            ]);

            if (isset($session->url)) {
                return new PaymentInit(
                    isAvailable: true,
                    returnUrl: $data->returnUrl,
                    persistentData: ['session' => $session->toArray()]
                );
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
    }

    /**
     * The url to the payment gateway.
     */
    public function getRedirectUrl(PaymentInit $init): Uri
    {
        return Uri::of($init->persistentData['session']['url']);
    }

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            externalRefId: 'test',
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::Stripe
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
            Stripe::setApiKey(Config::string('checkout.integrations.stripe.stripeSecretKey'));

            $session = Session::retrieve($request->input('session_id'));

            if ($session->payment_status === 'paid') {
                return new PaymentResult(
                    status: PaymentStatusEnum::Succeeded,
                    persistentData: ['session' => $session->toArray()]
                );
            }
        } catch (Throwable) {
            // nothing to do
        }

        return new PaymentResult(status: PaymentStatusEnum::Failed, persistentData: ['request' => $request->all()]);
    }

    /**
     * Shows the result of the payment process to the user.
     */
    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}
