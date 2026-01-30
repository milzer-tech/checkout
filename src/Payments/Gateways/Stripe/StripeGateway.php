<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Stripe;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Number;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
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
            Stripe::setApiKey(Config::string('checkout.integrations.stripe.secret_key'));

            $payload = [
                'locale' => $data->lang ?? 'auto',
                'payment_method_types' => ['card'],
                'customer_creation' => 'always',
                'customer_email' => $data->contact->email,
                'mode' => 'payment',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $data->price->currency,
                            'product_data' => [
                                'name' => 'The itinerary price',
                            ],
                            'unit_amount' => $data->price->toCent(),
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
            ];

            $session = Session::create($this->customizeSessionPayload($payload, $data->transaction));

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
        /** @phpstan-ignore-next-line */
        return Uri::of($init->persistentData['session']['url']);
    }

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            /** @phpstan-ignore-next-line */
            externalRefId: $paymentInit->persistentData['session']['id'],
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::Other,
            paymentMethodName: 'Stripe'
        );
    }

    /**
     * Handles the callback from the payment gateway.
     *
     * @param  array<string, mixed>|BaseDto  $persistentData
     */
    public function authorize(Request $request, BaseDto|array $persistentData): AuthorizationResult
    {
        try {
            Stripe::setApiKey(Config::string('checkout.integrations.stripe.secret_key'));

            $session = Session::retrieve($persistentData['session']['id']);
            $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

            if ($paymentIntent->status === 'requires_capture'
                && $paymentIntent->amount_capturable === $persistentData['session']['amount_total']) {
                return new AuthorizationResult(
                    isSuccessful: true,
                    resultData: [
                        'session' => $session->toArray(),
                        'payment_intent' => $paymentIntent->toArray(),
                    ]
                );
            }

        } catch (Throwable) {
            // nothing to do
        }

        return new AuthorizationResult(isSuccessful: false, resultData: ['request' => $request->all()]);
    }

    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        try {
            Stripe::setApiKey(Config::string('checkout.integrations.stripe.secret_key'));

            $intent = PaymentIntent::retrieve($resultData['payment_intent']['id'])->capture();

            $resultData['payment_intent'] = $intent->toArray();

            return new CaptureResult(isSuccessful: $intent->status === 'succeeded', persistentData: $resultData);
        } catch (Throwable $exception) {
            report($exception);
        }

        return new CaptureResult(isSuccessful: false, persistentData: $resultData);
    }

    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        try {
            Stripe::setApiKey(Config::string('checkout.integrations.stripe.secret_key'));

            $intent = PaymentIntent::retrieve($resultData['payment_intent']['id'])->cancel();

            $resultData['payment_intent'] = $intent->toArray();

            dd($resultData['payment_intent']);

            return new AbortResult(isSuccessful: $intent->status === 'succeeded', persistentData: $resultData);
        } catch (Throwable $exception) {
            report($exception);
        }

        return new AbortResult(isSuccessful: false, persistentData: $resultData);
    }

    /**
     * GetCustomize the Stripe session payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function customizeSessionPayload(array $payload, Transaction $transaction): array
    {
        if (Config::boolean('checkout.insurance.vertical.active')
            && isset($transaction->checkout->data['insurance'])
            && $transaction->checkout->data['insurance']['quote_id']
        ) {
            $config = [
                'payment_intent_data' => [
                    'setup_future_usage' => 'off_session',
                    'capture_method' => 'manual',
                    'metadata' => [
                        'transaction_id' => $transaction->id,
                        'checkout_id' => $transaction->checkout_id,
                    ],
                ],
                'custom_text' => [
                    'submit' => [
                        'message' => sprintf(
                            @trans('checkout::page.payment.additional_insurance_cost'),
                            Number::currency(
                                number: $transaction->checkout->data['insurance']['total'] / 100,
                                in: $transaction->checkout->data['insurance']['currency']
                            )
                        ),
                    ],
                ],
            ];
        }

        return array_merge($payload, $config ?? []);
    }
}
