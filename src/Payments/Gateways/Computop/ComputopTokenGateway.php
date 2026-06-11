<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Computop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Actions\Payment\CreateNezasaPaymentAuthorizationAction;
use Nezasa\Checkout\Integrations\Computop\Connectors\ComputopConnector;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopReversePaymentPayload;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\ComputopTokenPaymentPayload;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\CaptureInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\CaptureManualPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\ComputopAmountDto;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\OrderPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\UrlPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentAuthorizationPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaymentAuthorizationCardPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use RuntimeException;
use Throwable;

class ComputopTokenGateway implements RedirectPaymentContract
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.computop_token.active');
    }

    /**
     * Returns the name of the payment gateway.
     *
     * Important: This name will be used to identify the payment gateway in the checkout process
     * and it has to be unique, please check the previous gateways' names,
     */
    public static function name(): string
    {
        return Config::string('checkout.integrations.computop_token.name');
    }

    /**
     * Returns whether the payment gateway is tokenized.
     *
     * This gateway only authorizes the payment at Computop and sends the card token
     * to Nezasa, so Nezasa captures the money on their side.
     */
    public static function isTokenized(): bool
    {
        return true;
    }

    /**
     * Prepares the Computop hosted payment page with a CredentialOnFile setup intent.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        try {
            $orderDescription = Config::boolean('checkout.integrations.computop.test_mode')
                ? ['Test:0000']
                : ['The itinerary price'];

            $payload = new ComputopTokenPaymentPayload(
                transactionId: (string) $data->transaction->id,
                amount: new ComputopAmountDto($data->price->toCent(), $data->price->currency),
                order: new OrderPayloadEntity(id: $data->checkoutId, description: $orderDescription),
                urls: new UrlPayloadEntity(
                    success: (string) $data->returnUrl,
                    failure: ($data->returnUrl).'&failure=1',
                    cancel: (string) $data->cancelUrl,
                ),
                capture: new CaptureInfoPayloadEntity(
                    manual: new CaptureManualPayloadEntity(final: 'yes')
                ),
                language: $data->lang,
            );

            $response = ComputopConnector::make()->payment()->init($payload);

            if (! $response->failed()) {
                return new PaymentInit(
                    isAvailable: true,
                    returnUrl: $data->returnUrl,
                    persistentData: [
                        'response' => $response->array(),
                        'amount' => $payload->amount->toArray(),
                        'paylaod' => $payload->toArray(),
                    ]
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
        return Uri::of(
            data_get($init->persistentData, 'response._Links.redirect.href')
        );
    }

    /**
     * Verify the authorization at Computop and keep the payment details with the card token.
     */
    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        try {
            $response = ComputopConnector::make()->payment()->get($request->query('PayID'));

            return new AuthorizationResult(
                isSuccessful: $response->ok() && in_array($response->array('status'), ['CAPTURE_REQUEST', 'OK']),
                resultData: ['payment' => $response->array()]
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        return new AuthorizationResult(isSuccessful: false, resultData: (array) $request->query());
    }

    /**
     * Finalize the payment without capturing at Computop.
     *
     * The money stays only authorized; the card token (pseudo card number and
     * schemeReferenceId) is sent to Nezasa, so Nezasa captures it on their side.
     */
    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        try {
            $payload = $this->makePaymentAuthorizationPayload((array) ($resultData['payment'] ?? []));

            if (! $payload instanceof CreatePaymentAuthorizationPayload) {
                return new CaptureResult(isSuccessful: false, persistentData: $resultData);
            }

            /** @var Transaction $transaction */
            $transaction = $request->route('transaction');

            $paymentAuthorization = resolve(CreateNezasaPaymentAuthorizationAction::class)->run(
                checkoutId: $transaction->checkout->checkout_id,
                payload: $payload
            );

            $resultData['payment_authorization'] = $paymentAuthorization;

            return new CaptureResult(
                isSuccessful: $paymentAuthorization !== false,
                persistentData: $resultData
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        return new CaptureResult(isSuccessful: false, persistentData: $resultData);
    }

    /**
     * Abort the payment process by reversing the authorization at Computop.
     */
    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        try {
            $payload = new ComputopReversePaymentPayload(
                transactionId: (string) $resultData['payment']['transactionId'],
                amount: ComputopAmountDto::from($persistentData['amount'])
            );

            $response = ComputopConnector::make()->payment()->reverse($request->query('PayID'), $payload);
            $resultData['reverse'] = $response->array();

            return new AbortResult(
                isSuccessful: $response->ok() && in_array($response->array('status'), ['CAPTURE_REQUEST', 'OK']),
                persistentData: $resultData
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        return new AbortResult(isSuccessful: false, persistentData: $resultData);
    }

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     *
     * Note: For tokenized gateways no transaction is created in Nezasa by the callback
     * handlers; Nezasa creates it when capturing on their side. This method only exists
     * to fulfil the contract.
     */
    public function makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult): NezasaPayload
    {
        /** @var Transaction $transaction */
        $transaction = $request->route('transaction');

        return new NezasaPayload(
            externalRefId: $request->query('PayID', 'unknown'),
            amount: $transaction->price,
            paymentMethod: NezasaPaymentMethodEnum::Other,
            status: NezasaTransactionStatusEnum::Closed,
            paymentMethodName: self::name()
        );
    }

    /**
     * Build the Nezasa payment authorization payload from the Computop payment details.
     *
     * The live "get payment" response keeps the card under "payment.card" with the pseudo
     * card number in "number"/"PCNr" and the scheme reference in "payment.schemeReferenceID",
     * while the documented response uses "paymentMethods.card" with "pseudoCardNumber" and
     * "schemeReferenceId". Both shapes are supported.
     *
     * @param  array<string, mixed>  $payment
     */
    private function makePaymentAuthorizationPayload(array $payment): ?CreatePaymentAuthorizationPayload
    {
        $card = (array) data_get($payment, 'payment.card');
        $alias = (string) data_get($card, 'number');
        $schemeReferenceId = (string) data_get($payment, 'payment.schemeReferenceID');

        [$expiryMonth, $expiryYear] = $this->parseExpiryDate(
            (string) data_get($card, 'expiryDate')
        );

        if (blank($schemeReferenceId) || blank($alias) || $expiryMonth < 1 || $expiryYear < 1) {
            report(new RuntimeException('Computop token response is missing required card token fields.'));

            return null;
        }

        return new CreatePaymentAuthorizationPayload(
            aliasProvider: 'COMPUTOP',
            schemeReferenceId: $schemeReferenceId,
            card: new PaymentAuthorizationCardPayloadEntity(
                alias: $alias,
                brand: (string) (data_get($card, 'brand') ?? data_get($payment, 'payment.CCBrand', '')),
                expiryMonth: $expiryMonth,
                expiryYear: $expiryYear,
                cardHolderName: (string) (data_get($card, 'cardholderName') ?? data_get($payment, 'payment.CardHolder', '')),
                issuer: (string) data_get($card, 'issuer', ''),
            )
        );
    }

    /**
     * Parse the expiry date in "MM.DD.YYYY", "YYYY-MM" or Computop's "YYYYMM" format.
     *
     * @return array{0: int, 1: int}
     */
    private function parseExpiryDate(string $expiryDate): array
    {
        if (preg_match('/^(?<month>\d{1,2})\.(?:\d{1,2}\.)?(?<year>\d{4})$/', $expiryDate, $matches) === 1) {
            return [(int) $matches['month'], (int) $matches['year']];
        }

        if (preg_match('/^(?<year>\d{4})-(?<month>\d{1,2})/', $expiryDate, $matches) === 1) {
            return [(int) $matches['month'], (int) $matches['year']];
        }

        if (preg_match('/^(?<year>\d{4})(?<month>\d{2})$/', $expiryDate, $matches) === 1) {
            return [(int) $matches['month'], (int) $matches['year']];
        }

        return [0, 0];
    }
}
