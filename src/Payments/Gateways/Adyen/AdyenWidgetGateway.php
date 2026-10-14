<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Adyen;

use Adyen\Model\Checkout\Amount;
use Adyen\Model\Checkout\CreateCheckoutSessionRequest;
use Adyen\Model\Checkout\ModelInterface;
use Adyen\Model\Checkout\Payment;
use Adyen\Model\Checkout\PaymentCaptureRequest;
use Adyen\Model\Checkout\PaymentCaptureResponse;
use Adyen\Model\Checkout\PaymentCompletionDetails;
use Adyen\Model\Checkout\PaymentDetailsRequest;
use Adyen\Model\Checkout\PaymentReversalRequest;
use Adyen\Model\Checkout\PaymentReversalResponse;
use Adyen\Model\Checkout\SessionResultResponse;
use Adyen\Model\Checkout\ShopperName;
use Adyen\Service\Checkout\ModificationsApi;
use Adyen\Service\Checkout\PaymentsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Js;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Adyen\Exceptions\MissingAdyenCredentialsException;
use Nezasa\Checkout\Integrations\Adyen\Support\AdyenAmount;
use Nezasa\Checkout\Integrations\Adyen\Support\AdyenMerchantAccount;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use RuntimeException;
use Throwable;

/**
 * Adyen Drop-in with the sessions flow, using the Adyen API library.
 *
 * Every payment is processed with the credentials of its currency, see AdyenMerchantAccount.
 *
 * 1. prepare: creates a payment session with manual capture, for the merchant account of the currency
 * 2. getAssets: renders the Drop-in, which sends the shopper to the payment result page once the payment completed or failed
 * 3. authorize: verifies the payment with Adyen, by the session result of the Drop-in or the redirect result of a redirect
 * 4. capture: captures the authorized payment after the booking succeeded
 * 5. abort: reverses the authorized payment when the booking failed
 *
 * @see https://docs.adyen.com/online-payments/build-your-integration/sessions-flow?platform=Web&integration=Drop-in&version=6.46.0
 */
class AdyenWidgetGateway implements WidgetPaymentContract
{
    /**
     * The Drop-in locales of the checkout languages.
     */
    private const array LOCALES = [
        'de' => 'de-DE',
        'en' => 'en-US',
        'es' => 'es-ES',
        'fr' => 'fr-FR',
    ];

    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.adyen.active');
    }

    /**
     * Returns the name of the payment gateway.
     *
     * Important: This name will be used to identify the payment gateway in the checkout process
     * and it has to be unique, please check the previous gateways' names,
     */
    public static function name(): string
    {
        return Config::string('checkout.integrations.adyen.name');
    }

    /**
     * Returns whether the payment gateway is tokenized.
     */
    public static function isTokenized(): bool
    {
        return false;
    }

    /**
     * Prepares the payment initiation process.
     *
     * @throws MissingAdyenCredentialsException when the currency of the payment has no credentials.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        $account = AdyenMerchantAccount::forCurrency($data->price->currency);

        try {
            $amount = AdyenAmount::fromPrice($data->price);
            $reference = (string) $data->transaction->id;
            $locale = self::LOCALES[strtolower($data->lang ?? '')] ?? 'en-US';
            $countryCode = $this->countryCode($data->contact);

            $session = (new PaymentsApi($account->client()))->sessions(
                new CreateCheckoutSessionRequest([
                    'merchantAccount' => $account->merchantAccount,
                    'amount' => $amount,
                    'reference' => $reference,
                    'returnUrl' => (string) $data->returnUrl,
                    'expiresAt' => now()->addMinutes(Config::integer('checkout.payment_ttl')),
                    'shopperLocale' => $locale,
                    'countryCode' => $countryCode,
                    'shopperEmail' => $data->contact->email,
                    'shopperName' => $this->shopperName($data->contact),
                    'allowedPaymentMethods' => Config::array('checkout.integrations.adyen.allowed_payment_methods') ?: null,
                    'metadata' => [
                        'checkoutId' => mb_substr($data->checkoutId, 0, 80),
                        'itineraryId' => mb_substr($data->itineraryId, 0, 80),
                    ],
                    'channel' => CreateCheckoutSessionRequest::CHANNEL_WEB,
                    // Only authorize the payment, as it is captured after the booking succeeded.
                    'additionalData' => ['manualCapture' => 'true'],
                ]),
                ['idempotencyKey' => 'session-'.$reference]
            );

            $paymentData = new AdyenPaymentData(
                sessionId: $session->getId(),
                sessionData: $session->getSessionData(),
                credentials: $account->credentials,
                merchantAccount: $account->merchantAccount,
                reference: $reference,
                value: $amount->getValue(),
                currency: $amount->getCurrency(),
                locale: $locale,
                countryCode: $countryCode,
            );

            return new PaymentInit(
                isAvailable: true,
                returnUrl: $data->returnUrl,
                persistentData: $paymentData->toArray()
            );
        } catch (Throwable $throwable) {
            report($throwable);
        }

        return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
    }

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult): NezasaPayload
    {
        /** @var Transaction $transaction */
        $transaction = $request->route('transaction');

        return new NezasaPayload(
            externalRefId: $this->pspReference($captureResult->persistentData),
            amount: $transaction->price,
            paymentMethod: NezasaPaymentMethodEnum::Other,
            status: NezasaTransactionStatusEnum::Closed,
            paymentMethodName: 'Adyen'
        );
    }

    /**
     * Returns the assets required for the payment initiation process.
     */
    public function getAssets(PaymentInit $paymentInit): PaymentAsset
    {
        $paymentData = AdyenPaymentData::from($paymentInit->persistentData);
        $account = $this->account($paymentData);

        $sdkUrl = sprintf(
            'https://checkoutshopper-%s.cdn.adyen.com/checkoutshopper/sdk/%s',
            $account->environment,
            Config::string('checkout.integrations.adyen.web.version')
        );

        $configuration = array_filter([
            'session' => [
                'id' => $paymentData->sessionId,
                'sessionData' => $paymentData->sessionData,
            ],
            'environment' => $account->environment,
            'clientKey' => $account->clientKey,
            'amount' => ['value' => $paymentData->value, 'currency' => $paymentData->currency],
            'locale' => $paymentData->locale,
            'countryCode' => $paymentData->countryCode,
        ]);

        $scripts = [
            '<link rel="stylesheet" href="'.e($sdkUrl.'/adyen.css').'"
                integrity="'.e(Config::string('checkout.integrations.adyen.web.style_integrity')).'"
                crossorigin="anonymous">',
            '<script src="'.e($sdkUrl.'/adyen.js').'"
                integrity="'.e(Config::string('checkout.integrations.adyen.web.script_integrity')).'"
                crossorigin="anonymous">
            </script>',
            '<script>'.$this->dropInScript($configuration, $paymentInit->returnUrl).'</script>',
        ];

        return new PaymentAsset(isAvailable: true, scripts: $scripts, html: '<div id="adyen-dropin" wire:ignore></div>');
    }

    /**
     * Handles the callback from the payment gateway to authorize the payment.
     *
     * The Drop-in adds the session result to the return URL when the payment completed or failed on the
     * page, and Adyen adds the redirect result when the shopper comes back from a redirect, e.g. 3D Secure.
     *
     * Persistent data is the data that is returned from paymentInit in the prepare method.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        try {
            $paymentData = AdyenPaymentData::from($persistentData);
            $payments = new PaymentsApi($this->account($paymentData)->client());

            $redirectResult = $request->query('redirectResult');
            $sessionResult = $request->query('sessionResult');

            if (is_string($redirectResult) && $redirectResult !== '') {
                $details = $payments->paymentsDetails(new PaymentDetailsRequest([
                    'details' => new PaymentCompletionDetails(['redirectResult' => $redirectResult]),
                ]));

                $result = $this->toArray($details);
                $payment = $details->getMerchantReference() === $paymentData->reference
                    ? $this->payment($details->getPspReference(), $details->getResultCode(), $details->getAmount())
                    : null;
            } elseif (is_string($sessionResult) && $sessionResult !== '') {
                $session = $payments->getResultOfPaymentSession(
                    $paymentData->sessionId,
                    ['queryParams' => ['sessionResult' => $sessionResult]]
                );

                $result = $this->toArray($session);
                $payment = $this->sessionPayment($session, $paymentData);
            } else {
                return new AuthorizationResult(isSuccessful: false, resultData: ['reason' => 'missing_result']);
            }

            return new AuthorizationResult(
                isSuccessful: $payment !== null && $this->isAuthorised($payment, $paymentData),
                resultData: ['payment' => $payment, 'result' => $result]
            );
        } catch (Throwable $exception) {
            report($exception);

            return new AuthorizationResult(
                isSuccessful: false,
                resultData: ['reason' => 'exception', 'error' => $exception->getMessage()]
            );
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
            $paymentData = AdyenPaymentData::from($persistentData);

            $capture = (new ModificationsApi($this->account($paymentData)->client()))->captureAuthorisedPayment(
                $this->pspReference($resultData),
                new PaymentCaptureRequest([
                    'merchantAccount' => $paymentData->merchantAccount,
                    'amount' => $paymentData->amount(),
                    'reference' => $paymentData->reference,
                ]),
                ['idempotencyKey' => 'capture-'.$paymentData->reference]
            );

            $resultData['capture'] = $this->toArray($capture);

            // Adyen captures the payment asynchronously, after it received the capture.
            return new CaptureResult(
                isSuccessful: $capture->getStatus() === PaymentCaptureResponse::STATUS_RECEIVED,
                persistentData: $resultData
            );
        } catch (Throwable $exception) {
            report($exception);

            $resultData['capture'] = ['error' => $exception->getMessage()];
        }

        return new CaptureResult(isSuccessful: false, persistentData: $resultData);
    }

    /**
     * Abort the payment process. This method is called when the booking itinerary call fails.
     *
     * The reversal cancels the authorization, or refunds the payment if it was captured anyway.
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
            $paymentData = AdyenPaymentData::from($persistentData);

            $reversal = (new ModificationsApi($this->account($paymentData)->client()))->refundOrCancelPayment(
                $this->pspReference($resultData),
                new PaymentReversalRequest([
                    'merchantAccount' => $paymentData->merchantAccount,
                    'reference' => $paymentData->reference,
                ]),
                ['idempotencyKey' => 'reversal-'.$paymentData->reference]
            );

            $resultData['reversal'] = $this->toArray($reversal);

            return new AbortResult(
                isSuccessful: $reversal->getStatus() === PaymentReversalResponse::STATUS_RECEIVED,
                persistentData: $resultData
            );
        } catch (Throwable $exception) {
            report($exception);

            $resultData['reversal'] = ['error' => $exception->getMessage()];
        }

        return new AbortResult(isSuccessful: false, persistentData: $resultData);
    }

    /**
     * Get the merchant account that processes the payment, with the credentials the payment was created
     * with, so credentials that are added or enabled in the meantime do not change an open payment.
     */
    private function account(AdyenPaymentData $paymentData): AdyenMerchantAccount
    {
        $account = AdyenMerchantAccount::forCredentials($paymentData->credentials, $paymentData->currency);

        if ($account->merchantAccount !== $paymentData->merchantAccount) {
            throw new RuntimeException('The Adyen merchant account of the payment changed after the payment was created.');
        }

        return $account;
    }

    /**
     * Get the PSP reference of the authorized payment.
     *
     * @param  array<string, mixed>  $resultData
     */
    private function pspReference(array $resultData): string
    {
        $pspReference = data_get($resultData, 'payment.pspReference');

        return is_string($pspReference) && $pspReference !== ''
            ? $pspReference
            : throw new RuntimeException('The Adyen payment has no PSP reference.');
    }

    /**
     * Get the authorized payment of the session, which the shopper completed in the Drop-in.
     *
     * @return array{pspReference: ?string, resultCode: ?string, amount: ?array{value: int, currency: string}}|null
     */
    private function sessionPayment(SessionResultResponse $session, AdyenPaymentData $paymentData): ?array
    {
        if ($session->getStatus() !== SessionResultResponse::STATUS_COMPLETED || $session->getReference() !== $paymentData->reference) {
            return null;
        }

        $payments = collect($session->getPayments() ?? [])
            ->filter(fn (Payment $payment): bool => $payment->getResultCode() === Payment::RESULT_CODE_AUTHORISED);

        // A single payment of the whole amount is expected, the session does not split the payment.
        $payment = $payments->count() === 1 ? $payments->first() : null;

        return $payment instanceof Payment
            ? $this->payment($payment->getPspReference(), $payment->getResultCode(), $payment->getAmount())
            : null;
    }

    /**
     * Get the payment details that are stored as the authorization result.
     *
     * @return array{pspReference: ?string, resultCode: ?string, amount: ?array{value: int, currency: string}}
     */
    private function payment(?string $pspReference, ?string $resultCode, ?Amount $amount): array
    {
        return [
            'pspReference' => $pspReference,
            'resultCode' => $resultCode,
            'amount' => $amount instanceof Amount ? ['value' => $amount->getValue(), 'currency' => $amount->getCurrency()] : null,
        ];
    }

    /**
     * Check if the payment is authorized with the amount of the session, when Adyen returns the amount.
     *
     * @param  array{pspReference: ?string, resultCode: ?string, amount: ?array{value: int, currency: string}}  $payment
     */
    private function isAuthorised(array $payment, AdyenPaymentData $paymentData): bool
    {
        return $payment['resultCode'] === Payment::RESULT_CODE_AUTHORISED
            && filled($payment['pspReference'])
            && ($payment['amount'] === null || $payment['amount'] === ['value' => $paymentData->value, 'currency' => $paymentData->currency]);
    }

    /**
     * Get the data of an Adyen model, to store it on the transaction.
     *
     * @return array<array-key, mixed>
     */
    private function toArray(ModelInterface $model): array
    {
        $data = json_decode(json_encode($model, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);

        return is_array($data) ? $data : [];
    }

    /**
     * Get the two-letter country code of the contact's address.
     */
    private function countryCode(ContactInfoPayloadEntity $contact): ?string
    {
        $countryCode = strtoupper($contact->address?->getCountryCode() ?? '');

        return preg_match('/^[A-Z]{2}$/', $countryCode) === 1 ? $countryCode : null;
    }

    /**
     * Get the name of the shopper, as Adyen requires both names when it is sent.
     */
    private function shopperName(ContactInfoPayloadEntity $contact): ?ShopperName
    {
        if (blank($contact->firstName) || blank($contact->lastName)) {
            return null;
        }

        return new ShopperName(['firstName' => $contact->firstName, 'lastName' => $contact->lastName]);
    }

    /**
     * Get the script that mounts the Drop-in and sends the shopper to the payment result page,
     * where the payment is verified with Adyen, once the payment completed or failed.
     *
     * @param  array<string, mixed>  $configuration
     */
    private function dropInScript(array $configuration, Uri $returnUrl): string
    {
        return '
            document.addEventListener("DOMContentLoaded", async function () {
                const configuration = '.Js::from($configuration).';
                const returnUrl = '.Js::from($returnUrl->toStringable()->toString()).';
                const container = document.getElementById("adyen-dropin");

                const showResult = function (result) {
                    const url = new URL(returnUrl);

                    url.searchParams.set("sessionId", configuration.session.id);

                    if (result && result.sessionResult) {
                        url.searchParams.set("sessionResult", result.sessionResult);
                    }

                    window.location.assign(url.toString());
                };

                try {
                    const { AdyenCheckout, Dropin } = window.AdyenWeb;

                    const checkout = await AdyenCheckout({
                        ...configuration,
                        onPaymentCompleted: showResult,
                        onPaymentFailed: showResult,
                        onError: function (error) {
                            console.error("Adyen Drop-in error", error.name, error.message);
                        },
                    });

                    new Dropin(checkout).mount(container);
                } catch (error) {
                    console.error("The Adyen Drop-in could not be loaded", error);

                    container.textContent = '.Js::from(trans('checkout::page.payment.not_available_service')).';
                }
            });
        ';
    }
}
