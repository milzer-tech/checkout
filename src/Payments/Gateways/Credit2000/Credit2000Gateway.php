<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Credit2000;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Credit2000\Connectors\Credit2000Connector;
use Nezasa\Checkout\Integrations\Credit2000\Enums\Credit2000CurrencyEnum;
use Nezasa\Checkout\Integrations\Credit2000\Support\Credit2000Xml;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
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

/**
 * Credit2000 hosted payment page gateway (SOAP ASMX).
 *
 * Lifecycle aligned with Nezasa authorize → book → capture/abort:
 * 1. prepare: SendParamToCredit2000 (action_Type=5 approval-only) → redirect URL
 * 2. authorize: callback uid + getTokenAndApprovePro(uid) — live-verified Pro fields
 *    (binding + return_Code=000 + non-placeholder Approve + ValidDate + token)
 * 3. capture: CreditXML actionType=4 with token after Nezasa booking
 * 4. abort: CreditXML actionType=7 refund when a charge exists; otherwise leave the
 *    uncaptured ActionType 5 approval to expire (Credit2000 has no release API)
 *
 * prepare_action_type=4 (charge on payment page) is not supported.
 *
 * @see Credit2000 API PDF (SendParamToCredit2000 / getTokenAndApprove / CreditXML)
 */
class Credit2000Gateway implements RedirectPaymentContract
{
    private const string RETURN_OK = '000';

    private const string ACTION_TEST = '2';

    /** CreditXML capture / charge action — not a supported SendParams prepare mode. */
    private const string ACTION_CHARGE = '4';

    private const string ACTION_APPROVAL = '5';

    private const string ACTION_REFUND = '7';

    /** SendParam placeholder echoed by Pro until a real approval overwrites it. */
    private const string APPROVE_PLACEHOLDER = '0000000';

    /**
     * Checkout-local abort marker when no capture occurred and no provider void exists.
     * Credit2000 confirmed uncaptured ActionType 5 approvals expire automatically
     * (~3 business days); there is no documented release/cancel API for them.
     */
    private const string CANCEL_MODE_LEFT_TO_EXPIRE = 'uncaptured_approval_left_to_expire';

    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.credit2000.active');
    }

    public static function name(): string
    {
        return Config::string('checkout.integrations.credit2000.name');
    }

    public static function isTokenized(): bool
    {
        return false;
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        try {
            $currency = Credit2000CurrencyEnum::tryFromCurrencyCode($data->price->currency);

            if (! $currency instanceof Credit2000CurrencyEnum) {
                return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
            }

            $order = (string) $data->transaction->id;
            $subunits = $data->price->toCent();
            $total = Credit2000Xml::amountToMinorUnits($subunits);
            $firstName = $data->contact->firstName ?? 'Customer';
            $lastName = $data->contact->lastName ?? '';
            $clientName = trim($lastName.'/'.$firstName, '/');
            $tz = $data->contact->localIdNumber ?: '';

            $prepareAction = Config::string('checkout.integrations.credit2000.prepare_action_type');

            // Only ActionType 5 (approval) is supported for prepare. Reject test mode (2),
            // page charge (4), and any other value.
            if ($prepareAction !== self::ACTION_APPROVAL) {
                return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
            }

            $response = Credit2000Connector::make()->payment()->sendParams([
                'host' => (string) $data->returnUrl,
                'action_Type' => self::ACTION_APPROVAL,
                'total_Pyment' => $total,
                'first_Payment' => $total,
                'currency' => $currency->value,
                'client_Name' => $clientName,
                'tz_Number' => $tz,
                'product_Id' => Credit2000Xml::productId($order),
                'purchase_Type' => Config::string('checkout.integrations.credit2000.purchase_type'),
            ]);

            $paymentUrl = $response['payment_url'] ?? null;

            if (! is_string($paymentUrl) || $paymentUrl === '') {
                return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
            }

            return new PaymentInit(
                isAvailable: true,
                returnUrl: $data->returnUrl,
                persistentData: [
                    'payment_url' => $paymentUrl,
                    'order' => $order,
                    'product_id' => Credit2000Xml::productId($order),
                    'amount_subunits' => $subunits,
                    'total_pyment' => $total,
                    'currency' => $data->price->currency,
                    'currency_code' => $currency->value,
                    'checkout_id' => $data->checkoutId,
                    'client_name' => $clientName,
                    'tz_number' => $tz,
                    'prepare_action_type' => self::ACTION_APPROVAL,
                ]
            );
        } catch (Throwable $exception) {
            $this->reportFailure($exception);
        }

        return new PaymentInit(isAvailable: false, returnUrl: $data->returnUrl);
    }

    public function getRedirectUrl(PaymentInit $init): Uri
    {
        return Uri::of((string) data_get($init->persistentData, 'payment_url'));
    }

    public function makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult): NezasaPayload
    {
        /** @var Transaction $transaction */
        $transaction = $request->route('transaction');

        $externalRefId = (string) (
            data_get($captureResult->persistentData, 'capture.confirmationNumber')
            ?: data_get($captureResult->persistentData, 'token.approveNum')
            ?: data_get($captureResult->persistentData, 'callback.params')
            ?: data_get($captureResult->persistentData, 'order')
            ?: 'unknown'
        );

        return new NezasaPayload(
            externalRefId: $externalRefId,
            amount: $transaction->price,
            paymentMethod: NezasaPaymentMethodEnum::Other,
            status: NezasaTransactionStatusEnum::Closed,
            paymentMethodName: self::name()
        );
    }

    /**
     * @param  array<string, mixed>  $persistentData
     */
    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        try {
            $callback = $this->callbackParams($request);

            if ($callback === []) {
                return new AuthorizationResult(isSuccessful: false, resultData: ['reason' => 'missing_callback_params']);
            }

            $uid = (string) ($callback['params'] ?? $callback['Params'] ?? '');

            if ($uid === '') {
                return new AuthorizationResult(isSuccessful: false, resultData: [
                    'reason' => 'missing_uid',
                    'callback' => $callback,
                ]);
            }

            $tokenResponse = Credit2000Connector::make()->payment()->getTokenAndApprovePro($uid);

            $verificationFailure = $this->providerAuthorizationMismatch(
                $persistentData,
                $tokenResponse
            );

            if ($verificationFailure !== null) {
                return new AuthorizationResult(isSuccessful: false, resultData: [
                    'reason' => $verificationFailure,
                    'callback' => $callback,
                    'provider' => $this->sanitizeProviderFields($tokenResponse),
                ]);
            }

            return new AuthorizationResult(
                isSuccessful: true,
                resultData: [
                    'callback' => $callback,
                    'credit2000' => [
                        'uid' => $uid,
                        'token' => $tokenResponse['token'],
                        'approveNum' => $tokenResponse['approveNum'] ?? '',
                        'validDate' => $tokenResponse['validDate'] ?? '',
                        'cardType' => $tokenResponse['cardType'] ?? '1',
                        'customerId' => '000000001',
                        'return_Code' => $tokenResponse['return_Code'] ?? '',
                    ],
                ]
            );
        } catch (Throwable $exception) {
            $this->reportFailure($exception);
        }

        return new AuthorizationResult(isSuccessful: false, resultData: ['reason' => 'exception']);
    }

    /**
     * @param  array<string, mixed>  $persistentData
     * @param  array<string, mixed>  $resultData
     */
    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        try {
            if ($this->wasCaptured($resultData)) {
                return new CaptureResult(isSuccessful: true, persistentData: $resultData);
            }

            if ($this->wasAborted($resultData)) {
                return new CaptureResult(isSuccessful: false, persistentData: [
                    ...$resultData,
                    'capture_error' => 'already_aborted',
                ]);
            }

            $token = (string) data_get($resultData, 'credit2000.token', '');

            if ($token === '') {
                return new CaptureResult(isSuccessful: false, persistentData: [
                    ...$resultData,
                    'capture_error' => 'missing_token',
                ]);
            }

            $prepareAction = (string) ($persistentData['prepare_action_type'] ?? '');

            // Defense in depth: only ActionType 5 prepares may proceed to CreditXML charge.
            if ($prepareAction === self::ACTION_TEST) {
                return new CaptureResult(isSuccessful: false, persistentData: [
                    ...$resultData,
                    'capture_error' => 'test_mode_prepare_cannot_charge',
                ]);
            }

            if ($prepareAction !== self::ACTION_APPROVAL) {
                return new CaptureResult(isSuccessful: false, persistentData: [
                    ...$resultData,
                    'capture_error' => 'unsupported_prepare_action_type',
                ]);
            }

            [$month, $year] = $this->parseValidDate((string) data_get($resultData, 'credit2000.validDate', ''));
            $total = (string) ($persistentData['total_pyment'] ?? '');
            $currency = (string) ($persistentData['currency_code'] ?? '1');

            $capture = Credit2000Connector::make()->payment()->creditXml([
                'cardNumber' => $token,
                'validationMonth' => $month ?? '00',
                'validationYear' => $year ?? '00',
                'actionType' => self::ACTION_CHARGE,
                'totalPayment' => $total,
                'firstPayment' => $total,
                'currency' => $currency,
                'cardType' => (string) data_get($resultData, 'credit2000.cardType', '1'),
                'customerId' => (string) data_get($resultData, 'credit2000.customerId', '000000001'),
                'confirmationNumber' => (string) data_get($resultData, 'credit2000.approveNum', '00000') ?: '00000',
                'tzNumber' => (string) ($persistentData['tz_number'] ?? '00000000') ?: '00000000',
            ]);

            $resultData['capture'] = $capture;

            return new CaptureResult(
                isSuccessful: $this->isProviderSuccess((string) ($capture['returnCode'] ?? '')),
                persistentData: $resultData
            );
        } catch (Throwable $exception) {
            $this->reportFailure($exception);
        }

        return new CaptureResult(isSuccessful: false, persistentData: [
            ...$resultData,
            'capture_error' => 'exception',
        ]);
    }

    /**
     * @param  array<string, mixed>  $persistentData
     * @param  array<string, mixed>  $resultData
     */
    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        try {
            if ($this->wasAborted($resultData)) {
                return new AbortResult(isSuccessful: true, persistentData: $resultData);
            }

            $token = (string) data_get($resultData, 'credit2000.token', '');
            $alreadyCaptured = $this->wasCaptured($resultData);

            // No capture occurred: do not invent a provider release. Credit2000 has no
            // API to void uncaptured ActionType 5 approvals; they expire automatically.
            if ($token === '' || ! $alreadyCaptured) {
                $resultData['cancel'] = [
                    'mode' => self::CANCEL_MODE_LEFT_TO_EXPIRE,
                ];

                return new AbortResult(isSuccessful: true, persistentData: $resultData);
            }

            $total = (string) ($persistentData['total_pyment'] ?? '');
            $currency = (string) ($persistentData['currency_code'] ?? '1');
            [$month, $year] = $this->parseValidDate((string) data_get($resultData, 'credit2000.validDate', ''));

            $cancel = Credit2000Connector::make()->payment()->creditXml([
                'cardNumber' => $token,
                'validationMonth' => $month ?? '00',
                'validationYear' => $year ?? '00',
                'actionType' => self::ACTION_REFUND,
                'totalPayment' => $total,
                'firstPayment' => $total,
                'currency' => $currency,
                'cardType' => (string) data_get($resultData, 'credit2000.cardType', '1'),
                'customerId' => (string) data_get($resultData, 'credit2000.customerId', '000000001'),
                'confirmationNumber' => (string) (
                    data_get($resultData, 'capture.confirmationNumber')
                    ?: data_get($resultData, 'credit2000.approveNum')
                    ?: '00000'
                ),
                'tzNumber' => (string) ($persistentData['tz_number'] ?? '00000000') ?: '00000000',
            ]);

            $resultData['cancel'] = $cancel;

            return new AbortResult(
                isSuccessful: $this->isProviderSuccess((string) ($cancel['returnCode'] ?? '')),
                persistentData: $resultData
            );
        } catch (Throwable $exception) {
            $this->reportFailure($exception);
        }

        return new AbortResult(isSuccessful: false, persistentData: [
            ...$resultData,
            'abort_error' => 'exception',
        ]);
    }

    /**
     * @param  array<string, mixed>  $resultData
     */
    private function wasCaptured(array $resultData): bool
    {
        return $this->isProviderSuccess((string) data_get($resultData, 'capture.returnCode', ''));
    }

    /**
     * @param  array<string, mixed>  $resultData
     */
    private function wasAborted(array $resultData): bool
    {
        if (data_get($resultData, 'cancel.mode') === self::CANCEL_MODE_LEFT_TO_EXPIRE) {
            return true;
        }

        return $this->isProviderSuccess((string) data_get($resultData, 'cancel.returnCode', ''));
    }

    private function isProviderSuccess(string $returnCode): bool
    {
        return $returnCode === self::RETURN_OK || $returnCode === '0';
    }

    /**
     * Live-verified Pro authorization checks (ActionType 5).
     *
     * Pro provides binding fields and, on success, overwrites return_Code / Approve /
     * ValidDate and returns a token. Pro uID is not required — live success left it empty;
     * the callback UID is only the server-to-server lookup key.
     *
     * @param  array<string, mixed>  $persistentData
     * @param  array<string, string>  $tokenResponse
     */
    private function providerAuthorizationMismatch(
        array $persistentData,
        array $tokenResponse
    ): ?string {
        if (($tokenResponse['token'] ?? '') === '') {
            return 'token_missing';
        }

        $expectedProductId = (string) ($persistentData['product_id'] ?? '');
        $providerProductId = (string) ($tokenResponse['product_Id'] ?? '');

        if ($expectedProductId === '' || $providerProductId === '' || $providerProductId !== $expectedProductId) {
            return 'product_id_mismatch';
        }

        $expectedTotal = (string) ($persistentData['total_pyment'] ?? '');
        $providerTotal = (string) ($tokenResponse['total_Pyment'] ?? '');

        if ($expectedTotal === '' || $providerTotal === '' || $providerTotal !== $expectedTotal) {
            return 'amount_mismatch';
        }

        $expectedCurrency = (string) ($persistentData['currency_code'] ?? '');
        $providerCurrency = (string) ($tokenResponse['currency'] ?? '');

        if ($expectedCurrency === '' || $providerCurrency === '' || $providerCurrency !== $expectedCurrency) {
            return 'currency_mismatch';
        }

        $expectedAction = (string) ($persistentData['prepare_action_type'] ?? '');
        $providerAction = (string) ($tokenResponse['action_Type'] ?? '');

        if ($expectedAction === '' || $providerAction === '' || $providerAction !== $expectedAction) {
            return 'action_type_mismatch';
        }

        if ($providerAction !== self::ACTION_APPROVAL) {
            return 'action_type_not_approval';
        }

        $returnCode = (string) ($tokenResponse['return_Code'] ?? '');

        if ($returnCode !== self::RETURN_OK) {
            return 'return_code_not_approved';
        }

        $approveNum = (string) ($tokenResponse['approveNum'] ?? '');

        if ($approveNum === '') {
            return 'approve_missing';
        }

        if ($approveNum === self::APPROVE_PLACEHOLDER) {
            return 'approve_placeholder';
        }

        $validDate = (string) ($tokenResponse['validDate'] ?? '');
        [$month, $year] = $this->parseValidDate($validDate);

        if ($month === null || $year === null) {
            return 'valid_date_missing';
        }

        return null;
    }

    /**
     * Persist only non-sensitive provider echo fields used for mismatch diagnostics.
     *
     * @param  array<string, string>  $tokenResponse
     * @return array<string, string>
     */
    private function sanitizeProviderFields(array $tokenResponse): array
    {
        return array_filter([
            'product_Id' => (string) ($tokenResponse['product_Id'] ?? ''),
            'total_Pyment' => (string) ($tokenResponse['total_Pyment'] ?? ''),
            'currency' => (string) ($tokenResponse['currency'] ?? ''),
            'action_Type' => (string) ($tokenResponse['action_Type'] ?? ''),
            'return_Code' => (string) ($tokenResponse['return_Code'] ?? ''),
            'Approve' => (string) ($tokenResponse['approveNum'] ?? ''),
            'ValidDate' => (string) ($tokenResponse['validDate'] ?? ''),
            'http_status' => (string) ($tokenResponse['http_status'] ?? ''),
        ], static fn (string $value): bool => $value !== '');
    }

    /**
     * @return array{0: ?string, 1: ?string} [MM, YY]
     */
    private function parseValidDate(string $validDate): array
    {
        if (! preg_match('/^\d{4}$/', $validDate)) {
            return [null, null];
        }

        return [substr($validDate, 0, 2), substr($validDate, 2, 2)];
    }

    private function reportFailure(Throwable $exception): void
    {
        $message = $exception->getMessage();
        $message = preg_replace('/(company_Key|ClientKey|cardNumber|token)=[^&\s"\']*/i', '$1=redacted', $message) ?? $message;
        $message = preg_replace('#https?://[^\s]+#i', '[redacted-url]', $message) ?? $message;

        report(new RuntimeException('Credit2000 request failed: '.$exception::class.': '.$message));
    }

    /**
     * @return array<string, string>
     */
    private function callbackParams(Request $request): array
    {
        /** @var array<string, string> $params */
        $params = [];

        foreach (array_merge($request->query(), $request->request->all()) as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            if (in_array($key, ['checkoutId', 'itineraryId', 'origin', 'lang', 'rest-payment', 'transaction'], true)) {
                continue;
            }

            $params[(string) $key] = (string) $value;
        }

        return $params;
    }
}
