<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Actions\Checkout\BookItineraryAction;
use Nezasa\Checkout\Actions\Checkout\FindBookingResultAction;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Payment\CreateNezasaTransactionAction;
use Nezasa\Checkout\Actions\Transaction\UpdateTransactionAction;
use Nezasa\Checkout\Integrations\Adyen\Exceptions\MissingAdyenCredentialsException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\CreatePaymentTransactionRequest;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\BookingStatusEnum;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Adyen\AdyenWidgetGateway;
use Nezasa\Checkout\Payments\Gateways\Invoice\InvoiceGateway;
use Nezasa\Checkout\Payments\Handlers\DownPaymentCallBackHandler;
use Nezasa\Checkout\Payments\Handlers\PaymentInitiationHandler;
use Nezasa\Checkout\Payments\Handlers\RestPaymentCallBackHandler;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

const ADYEN_TEST_URL = 'https://checkout-test.adyen.com/v71';

beforeEach(function (): void {
    configureAdyen();
    Http::preventStrayRequests();
});

function adyenGatewayCheckout(array $contact = [], bool $restPayment = false): Checkout
{
    return Checkout::factory()->create([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'IBE',
        'lang' => 'de',
        'rest_payment' => $restPayment,
        'data' => [
            'status' => Checkout::buildSectionStatus(),
            'contact' => array_replace([
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'gender' => GenderEnum::Female,
                'email' => 'jane@example.test',
                'country' => 'DE-Germany',
                'countryCode' => 'DE',
                'city' => 'Berlin',
                'postalCode' => '10115',
                'street1' => 'Main Street',
            ], $contact),
            'paxInfo' => [],
        ],
    ]);
}

function adyenGatewayTransaction(string $currency = 'EUR', float $amount = 199.9, ?Checkout $checkout = null): Transaction
{
    return Transaction::create([
        'checkout_id' => ($checkout ?? adyenGatewayCheckout())->id,
        'gateway' => 'Adyen',
        'amount' => $amount,
        'currency' => $currency,
        'status' => TransactionStatusEnum::Pending,
    ]);
}

function adyenGatewayPrepareData(Transaction $transaction): PaymentPrepareData
{
    return new PaymentPrepareData(
        transaction: $transaction,
        returnUrl: Uri::of('https://checkout.example.test/checkout/result/'.$transaction->id.'?checkoutId=checkout-1&lang=de'),
        cancelUrl: Uri::of('https://checkout.example.test/checkout/details'),
        contact: ContactInfoPayloadEntity::from($transaction->checkout->data['contact']),
        price: $transaction->price,
        checkoutId: $transaction->checkout->checkout_id,
        itineraryId: $transaction->checkout->itinerary_id,
        origin: $transaction->checkout->origin,
        lang: $transaction->checkout->lang,
    );
}

function adyenGatewayRequest(Transaction $transaction, array $query = []): Request
{
    $request = Request::create('/checkout/result/'.$transaction->id, 'GET', $query);
    $request->setRouteResolver(fn (): object => new readonly class($transaction)
    {
        public function __construct(private Transaction $transaction) {}

        public function parameter(string $name): ?Transaction
        {
            return $name === 'transaction' ? $this->transaction : null;
        }
    });

    return $request;
}

/**
 * The session Adyen creates for the payment.
 */
function adyenGatewaySession(string $sessionData = 'Ab02b4c...'): array
{
    return [
        'id' => 'CS-SESSION-1',
        'sessionData' => $sessionData,
        'expiresAt' => '2026-10-14T12:00:00+00:00',
        'amount' => ['value' => 19990, 'currency' => 'EUR'],
        'merchantAccount' => 'MerchantEUR',
        'reference' => 'reference',
        'returnUrl' => 'https://checkout.example.test/checkout/result',
    ];
}

/**
 * The result of a session that the shopper completed with an authorized payment.
 */
function adyenGatewaySessionResult(Transaction $transaction, array $overrides = [], array $payment = []): array
{
    return array_replace([
        'id' => 'CS-SESSION-1',
        'status' => 'completed',
        'reference' => $transaction->id,
        'payments' => [
            array_replace([
                'pspReference' => 'PSP123',
                'resultCode' => 'Authorised',
                'amount' => ['value' => 19990, 'currency' => 'EUR'],
                'paymentMethod' => ['brand' => 'visa', 'type' => 'scheme'],
            ], $payment),
        ],
    ], $overrides);
}

/**
 * Prepare a payment and store its payment data on the transaction, like the payment initiation does.
 *
 * @return array{0: Transaction, 1: PaymentInit}
 */
function adyenGatewayPreparedPayment(string $currency = 'EUR', ?Checkout $checkout = null): array
{
    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession(), 201)]);

    $transaction = adyenGatewayTransaction($currency, checkout: $checkout);
    $init = (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData($transaction));
    $transaction->update(['prepare_data' => $init->persistentData]);

    expect($init->isAvailable)->toBeTrue();

    return [$transaction, $init];
}

/**
 * Get the JSON body that was sent to Adyen.
 */
function adyenRequestBody(ClientRequest $request): array
{
    return json_decode($request->body(), true) ?? [];
}

/**
 * Replace the faked Adyen responses.
 */
function fakeAdyen(array $responses): void
{
    Http::swap(new Factory);
    Http::preventStrayRequests();
    Http::fake($responses);
}

it('implements the widget contract and reads its activation and name from the config', function (): void {
    Config::set('checkout.integrations.adyen.name', 'Credit card (Adyen)');

    expect(is_subclass_of(AdyenWidgetGateway::class, WidgetPaymentContract::class))->toBeTrue()
        ->and(AdyenWidgetGateway::isActive())->toBeTrue()
        ->and(AdyenWidgetGateway::name())->toBe('Credit card (Adyen)')
        ->and(AdyenWidgetGateway::isTokenized())->toBeFalse();

    Config::set('checkout.integrations.adyen.active', false);

    expect(AdyenWidgetGateway::isActive())->toBeFalse();
});

it('offers Adyen for every currency', function (): void {
    Config::set('checkout.payment', [InvoiceGateway::class, AdyenWidgetGateway::class]);
    Config::set('checkout.integrations.invoice.active', true);
    Config::set('checkout.integrations.invoice.name', 'Invoice');

    expect(collect((new GetPaymentProviderAction)->run())->map->decryptGateway()->all())->toBe(['Invoice', 'Adyen']);
});

it('creates a manual capture session with the merchant account of the payment currency', function (): void {
    Config::set('checkout.payment_ttl', 45);
    $this->freezeTime();

    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession(), 201)]);

    $transaction = adyenGatewayTransaction();
    $checkout = $transaction->checkout;
    $prepareData = adyenGatewayPrepareData($transaction);
    $init = (new AdyenWidgetGateway)->prepare($prepareData);

    Http::assertSentCount(1);
    Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
        && $request->url() === ADYEN_TEST_URL.'/sessions'
        && $request->hasHeader('x-api-key', 'eur-api-key')
        && $request->hasHeader('Idempotency-Key', 'session-'.$transaction->id));

    $body = adyenRequestBody(Http::recorded()->first()[0]);

    expect($body)->toEqual([
        'merchantAccount' => 'MerchantEUR',
        'amount' => ['value' => 19990, 'currency' => 'EUR'],
        'reference' => $transaction->id,
        'returnUrl' => 'https://checkout.example.test/checkout/result/'.$transaction->id.'?checkoutId=checkout-1&lang=de',
        'expiresAt' => now()->addMinutes(45)->format(DATE_ATOM),
        'shopperLocale' => 'de-DE',
        'countryCode' => 'DE',
        'shopperEmail' => 'jane@example.test',
        'shopperName' => ['firstName' => 'Jane', 'lastName' => 'Doe'],
        'allowedPaymentMethods' => ['scheme'],
        'metadata' => [
            'checkoutId' => $checkout->checkout_id,
            'itineraryId' => $checkout->itinerary_id,
        ],
        'channel' => 'Web',
        'additionalData' => ['manualCapture' => 'true'],
        'applicationInfo' => [
            'externalPlatform' => ['name' => 'Nezasa'],
            'merchantApplication' => ['name' => 'Milzer Checkout'],
        ],
    ])
        ->and($init->isAvailable)->toBeTrue()
        ->and($init->returnUrl)->toBe($prepareData->returnUrl)
        ->and($init->persistentData)->toBe([
            'sessionId' => 'CS-SESSION-1',
            'sessionData' => 'Ab02b4c...',
            'credentials' => 'EUR',
            'merchantAccount' => 'MerchantEUR',
            'reference' => $transaction->id,
            'value' => 19990,
            'currency' => 'EUR',
            'locale' => 'de-DE',
            'countryCode' => 'DE',
        ]);
});

it('creates the session of a USD payment with the USD merchant account and its own API key', function (): void {
    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession(), 201)]);

    $init = (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction('USD', 1250.45)));

    Http::assertSent(fn (ClientRequest $request): bool => $request->hasHeader('x-api-key', 'usd-api-key')
        && adyenRequestBody($request)['merchantAccount'] === 'MerchantUSD'
        && adyenRequestBody($request)['amount'] === ['currency' => 'USD', 'value' => 125045]);

    expect($init->isAvailable)->toBeTrue()
        ->and($init->persistentData['merchantAccount'])->toBe('MerchantUSD')
        ->and($init->persistentData['value'])->toBe(125045)
        ->and($init->persistentData['currency'])->toBe('USD');
});

it('leaves out the shopper details that the contact does not have', function (): void {
    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession(), 201)]);

    $checkout = adyenGatewayCheckout(['firstName' => null, 'country' => null, 'countryCode' => null]);
    $checkout->update(['lang' => null]);

    (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction(checkout: $checkout)));

    $body = adyenRequestBody(Http::recorded()->first()[0]);

    expect($body)->not->toHaveKeys(['countryCode', 'shopperName'])
        ->and($body['shopperLocale'])->toBe('en-US')
        ->and($body['shopperEmail'])->toBe('jane@example.test');
});

it('uses the configured country code for Apple Pay when the contact has no country', function (): void {
    Config::set('checkout.integrations.adyen.country_code', ' de ');

    [, $init] = adyenGatewayPreparedPayment(checkout: adyenGatewayCheckout(['country' => null, 'countryCode' => null]));
    $script = (new AdyenWidgetGateway)->getAssets($init)->scripts->last();

    expect(adyenRequestBody(Http::recorded()->first()[0])['countryCode'])->toBe('DE')
        ->and($init->persistentData['countryCode'])->toBe('DE')
        ->and($script)->toContain('\u0022countryCode\u0022:\u0022DE\u0022');
});

it('prefers the country of the contact over the configured country code', function (): void {
    Config::set('checkout.integrations.adyen.country_code', 'DE');

    [, $init] = adyenGatewayPreparedPayment(checkout: adyenGatewayCheckout(['country' => 'CH-Switzerland', 'countryCode' => 'CH']));

    expect(adyenRequestBody(Http::recorded()->first()[0])['countryCode'])->toBe('CH')
        ->and($init->persistentData['countryCode'])->toBe('CH');
});

it('ignores a configured country code that is not two letters', function (mixed $countryCode): void {
    Config::set('checkout.integrations.adyen.country_code', $countryCode);

    [, $init] = adyenGatewayPreparedPayment(checkout: adyenGatewayCheckout(['country' => null, 'countryCode' => null]));

    expect(adyenRequestBody(Http::recorded()->first()[0]))->not->toHaveKey('countryCode')
        ->and($init->persistentData['countryCode'])->toBeNull();
})->with(['empty' => '', 'three letters' => 'DEU', 'number' => 49]);

it('offers all payment methods enabled in Adyen when no payment method is configured', function (): void {
    Config::set('checkout.integrations.adyen.allowed_payment_methods', []);

    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession(), 201)]);

    (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction()));

    expect(adyenRequestBody(Http::recorded()->first()[0]))->not->toHaveKey('allowedPaymentMethods');
});

it('throws an exception for a payment in a currency without credentials and does not call Adyen', function (): void {
    Http::fake();

    expect(fn (): PaymentInit => (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction('CHF'))))
        ->toThrow(MissingAdyenCredentialsException::class, 'No Adyen credentials are configured for CHF or enable using the default credentials.');

    Http::assertNothingSent();
});

it('throws the exception through the payment page instead of reporting the gateway as not available', function (): void {
    Http::fake();

    expect(fn (): mixed => (new PaymentInitiationHandler)->run(
        model: adyenGatewayCheckout(),
        price: new Price(199.9, 'CHF'),
        gateway: new AdyenWidgetGateway
    ))->toThrow(MissingAdyenCredentialsException::class, 'No Adyen credentials are configured for CHF or enable using the default credentials.');

    Http::assertNothingSent();
});

it('pays a currency without credentials with the default credentials when enabled', function (): void {
    Config::set('checkout.integrations.adyen.use_default_credentials', true);

    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession(), 201)]);

    $init = (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction('CHF')));
    $scripts = (new AdyenWidgetGateway)->getAssets($init)->scripts->implode("\n");

    Http::assertSent(fn (ClientRequest $request): bool => $request->hasHeader('x-api-key', 'default-api-key')
        && adyenRequestBody($request)['merchantAccount'] === 'MerchantDefault'
        && adyenRequestBody($request)['amount'] === ['currency' => 'CHF', 'value' => 19990]);

    expect($init->persistentData['credentials'])->toBe('default')
        ->and($init->persistentData['merchantAccount'])->toBe('MerchantDefault')
        ->and($scripts)->toContain('test_default-client-key')
        ->and($scripts)->not->toContain('test_eur-client-key');
});

it('is not available when Adyen does not create the session', function (): void {
    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response([
        'status' => 422,
        'errorCode' => '14_030',
        'message' => 'Return URL is missing.',
        'errorType' => 'validation',
    ], 422)]);

    $init = (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction()));

    expect($init->isAvailable)->toBeFalse();
});

it('renders the Drop-in with the session, the client key and the pinned Adyen Web version', function (): void {
    [, $init] = adyenGatewayPreparedPayment();

    $asset = (new AdyenWidgetGateway)->getAssets($init);
    $scripts = $asset->scripts->implode("\n");

    expect($asset->isAvailable)->toBeTrue()
        ->and($asset->html)->toBe('<div id="adyen-dropin" wire:ignore></div>')
        ->and($asset->scripts)->toHaveCount(3)
        ->and($scripts)->toContain('href="https://checkoutshopper-test.cdn.adyen.com/checkoutshopper/sdk/6.46.0/adyen.css"')
        ->and($scripts)->toContain('integrity="sha384-style-hash"')
        ->and($scripts)->toContain('src="https://checkoutshopper-test.cdn.adyen.com/checkoutshopper/sdk/6.46.0/adyen.js"')
        ->and($scripts)->toContain('integrity="sha384-script-hash"')
        ->and($scripts)->toContain('CS-SESSION-1')
        ->and($scripts)->toContain('Ab02b4c...')
        ->and($scripts)->toContain('test_eur-client-key')
        ->and($scripts)->toContain('de-DE')
        ->and($scripts)->toContain('19990')
        ->and($scripts)->toContain('onPaymentCompleted: showResult')
        ->and($scripts)->toContain('onPaymentFailed: showResult')
        ->and($scripts)->not->toContain('eur-api-key');
});

it('renders the Drop-in of a USD payment with the client key and environment of the USD account', function (): void {
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.environment', 'live-us');
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.live_url_prefix', 'usd-prefix');

    Http::fake(['usd-prefix-checkout-live.adyenpayments.com/checkout/v71/sessions' => Http::response(adyenGatewaySession(), 201)]);

    $init = (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction('USD')));
    $scripts = (new AdyenWidgetGateway)->getAssets($init)->scripts->implode("\n");

    expect($scripts)->toContain('https://checkoutshopper-live-us.cdn.adyen.com/checkoutshopper/sdk/6.46.0/adyen.js')
        ->and($scripts)->toContain('test_usd-client-key')
        ->and($scripts)->toContain('live-us')
        ->and($scripts)->not->toContain('test_eur-client-key')
        ->and($scripts)->not->toContain('usd-api-key');
});

it('validates Apple Pay for the host of the page that embeds the checkout', function (): void {
    [, $init] = adyenGatewayPreparedPayment();

    $script = (new AdyenWidgetGateway)->getAssets($init)->scripts->last();

    expect($script)->toContain('const ancestors = window.location.ancestorOrigins;')
        ->and($script)->toContain('new URL(ancestors[ancestors.length - 1]).hostname')
        ->and($script)->toContain('paymentMethodsConfiguration: topLevelHost ? { applepay: { domainName: topLevelHost } } : {}');
});

it('embeds the Drop-in configuration as escaped JSON', function (): void {
    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession('</script><script>alert(1)</script>'), 201)]);

    $init = (new AdyenWidgetGateway)->prepare(adyenGatewayPrepareData(adyenGatewayTransaction()));
    $script = (new AdyenWidgetGateway)->getAssets($init)->scripts->last();

    expect($script)->toContain('JSON.parse(')
        ->and(substr_count((string) $script, '</script>'))->toBe(1);
});

it('authorizes the payment that the shopper completed in the Drop-in with the session result', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction))]);

    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'sessionResult' => 'SESSION+RESULT==']),
        $init->persistentData
    );

    Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'GET'
        && $request->url() === ADYEN_TEST_URL.'/sessions/CS-SESSION-1?sessionResult=SESSION%2BRESULT%3D%3D'
        && $request->hasHeader('x-api-key', 'eur-api-key'));

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->resultData['payment'])->toBe([
            'pspReference' => 'PSP123',
            'resultCode' => 'Authorised',
            'amount' => ['value' => 19990, 'currency' => 'EUR'],
        ])
        ->and($result->resultData['result']['status'])->toBe('completed');
});

it('verifies the session of the stored payment data, not the session in the return URL', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction))]);

    (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-OTHER-SESSION', 'sessionResult' => 'SESSION-RESULT']),
        $init->persistentData
    );

    Http::assertSent(fn (ClientRequest $request): bool => str_starts_with($request->url(), ADYEN_TEST_URL.'/sessions/CS-SESSION-1?'));
});

it('does not authorize a session without an authorized payment of the whole amount', function (array $overrides, array $payment): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction, $overrides, $payment))]);

    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'sessionResult' => 'SESSION-RESULT']),
        $init->persistentData
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->resultData)->toHaveKeys(['payment', 'result']);
})->with([
    'refused session' => [['status' => 'refused'], []],
    'canceled session' => [['status' => 'canceled'], []],
    'expired session' => [['status' => 'expired'], []],
    'pending payment' => [['status' => 'paymentPending'], ['resultCode' => 'Pending']],
    'received payment' => [[], ['resultCode' => 'Received']],
    'payment without psp reference' => [[], ['pspReference' => null]],
    'lower amount' => [[], ['amount' => ['value' => 100, 'currency' => 'EUR']]],
    'other currency' => [[], ['amount' => ['value' => 19990, 'currency' => 'USD']]],
    'session of another payment' => [['reference' => 'another-transaction'], []],
    'no payments' => [['payments' => []], []],
]);

it('does not authorize a session with more than one authorized payment', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    $payment = ['pspReference' => 'PSP123', 'resultCode' => 'Authorised', 'amount' => ['value' => 19990, 'currency' => 'EUR']];

    fakeAdyen([ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction, [
        'payments' => [$payment, [...$payment, 'pspReference' => 'PSP456']],
    ]))]);

    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'sessionResult' => 'SESSION-RESULT']),
        $init->persistentData
    );

    expect($result->isSuccessful)->toBeFalse();
});

it('does not authorize a session result that Adyen rejects', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([ADYEN_TEST_URL.'/sessions/*' => Http::response([
        'status' => 422,
        'errorCode' => '14_0417',
        'message' => 'Invalid session result.',
        'errorType' => 'validation',
    ], 422)]);

    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'sessionResult' => 'INVALID']),
        $init->persistentData
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->resultData)->toBe(['reason' => 'exception', 'error' => 'Invalid session result.']);
});

it('authorizes the payment that the shopper was redirected back from with the redirect result', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([ADYEN_TEST_URL.'/payments/details' => Http::response([
        'resultCode' => 'Authorised',
        'pspReference' => 'PSP789',
        'merchantReference' => $transaction->id,
        'amount' => ['value' => 19990, 'currency' => 'EUR'],
    ])]);

    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'redirectResult' => 'X6XtfGC3!Y', 'sessionResult' => 'SESSION-RESULT']),
        $init->persistentData
    );

    Http::assertSentCount(1);
    Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
        && $request->url() === ADYEN_TEST_URL.'/payments/details'
        && adyenRequestBody($request) === ['details' => ['redirectResult' => 'X6XtfGC3!Y']]);

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->resultData['payment'])->toBe([
            'pspReference' => 'PSP789',
            'resultCode' => 'Authorised',
            'amount' => ['value' => 19990, 'currency' => 'EUR'],
        ]);
});

it('does not authorize a redirect result of another or an unauthorized payment', function (array $details): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([ADYEN_TEST_URL.'/payments/details' => Http::response(array_replace([
        'resultCode' => 'Authorised',
        'pspReference' => 'PSP789',
        'merchantReference' => $transaction->id,
        'amount' => ['value' => 19990, 'currency' => 'EUR'],
    ], $details))]);

    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'redirectResult' => 'X6XtfGC3!Y']),
        $init->persistentData
    );

    expect($result->isSuccessful)->toBeFalse();
})->with([
    'refused' => [['resultCode' => 'Refused', 'refusalReason' => 'Refused']],
    'cancelled' => [['resultCode' => 'Cancelled']],
    'another payment' => [['merchantReference' => 'another-transaction']],
    'other amount' => [['amount' => ['value' => 1, 'currency' => 'EUR']]],
]);

it('does not authorize without a result from the Drop-in or Adyen', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([]);

    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1']),
        $init->persistentData
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->resultData)->toBe(['reason' => 'missing_result']);

    Http::assertNothingSent();
});

it('does not authorize when the stored payment data is not valid', function (): void {
    $result = (new AdyenWidgetGateway)->authorize(
        adyenGatewayRequest(adyenGatewayTransaction(), ['sessionResult' => 'SESSION-RESULT']),
        ['sessionId' => ['invalid']]
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->resultData['reason'])->toBe('exception');
});

it('keeps processing an open payment with the credentials it was created with', function (): void {
    Config::set('checkout.integrations.adyen.use_default_credentials', true);
    Config::set('checkout.integrations.adyen.merchant_accounts.USD');

    [$transaction, $init] = adyenGatewayPreparedPayment('USD');

    // The USD credentials are added while the payment is open.
    Config::set('checkout.integrations.adyen.merchant_accounts.USD', [
        'merchant_account' => 'MerchantUSD',
        'api_key' => 'usd-api-key',
        'client_key' => 'test_usd-client-key',
    ]);

    fakeAdyen([
        ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction, [], ['amount' => ['value' => 19990, 'currency' => 'USD']])),
        ADYEN_TEST_URL.'/payments/PSP123/captures' => Http::response(['status' => 'received', 'pspReference' => 'CAPTURE123'], 201),
    ]);

    $request = adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'sessionResult' => 'SESSION-RESULT']);
    $authorized = (new AdyenWidgetGateway)->authorize($request, $init->persistentData);
    $captured = (new AdyenWidgetGateway)->capture($request, $init->persistentData, $authorized->resultData);

    expect($init->persistentData['credentials'])->toBe('default')
        ->and($authorized->isSuccessful)->toBeTrue()
        ->and($captured->isSuccessful)->toBeTrue();

    Http::assertSentCount(2);
    Http::assertSent(fn (ClientRequest $request): bool => str_contains($request->url(), '/captures')
        && $request->hasHeader('x-api-key', 'default-api-key')
        && adyenRequestBody($request)['merchantAccount'] === 'MerchantDefault');
    Http::assertNotSent(fn (ClientRequest $request): bool => $request->hasHeader('x-api-key', 'usd-api-key'));
});

it('never mixes credentials when the merchant account of an open payment was changed', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();
    $resultData = ['payment' => ['pspReference' => 'PSP123', 'resultCode' => 'Authorised']];

    Config::set('checkout.integrations.adyen.merchant_accounts.EUR.merchant_account', 'AnotherMerchantEUR');

    fakeAdyen([]);

    $captured = (new AdyenWidgetGateway)->capture(adyenGatewayRequest($transaction), $init->persistentData, $resultData);
    $aborted = (new AdyenWidgetGateway)->abort(adyenGatewayRequest($transaction), $init->persistentData, $resultData);

    expect($captured->isSuccessful)->toBeFalse()
        ->and($captured->persistentData['capture'])->toBe(['error' => 'The Adyen merchant account of the payment changed after the payment was created.'])
        ->and($aborted->isSuccessful)->toBeFalse();

    Http::assertNothingSent();
});

it('captures the authorized payment with the merchant account that authorized it', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();
    $resultData = ['payment' => ['pspReference' => 'PSP123', 'resultCode' => 'Authorised', 'amount' => ['value' => 19990, 'currency' => 'EUR']]];

    fakeAdyen([ADYEN_TEST_URL.'/payments/PSP123/captures' => Http::response([
        'merchantAccount' => 'MerchantEUR',
        'paymentPspReference' => 'PSP123',
        'pspReference' => 'CAPTURE123',
        'reference' => $transaction->id,
        'status' => 'received',
        'amount' => ['value' => 19990, 'currency' => 'EUR'],
    ], 201)]);

    $result = (new AdyenWidgetGateway)->capture(adyenGatewayRequest($transaction), $init->persistentData, $resultData);

    Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
        && $request->url() === ADYEN_TEST_URL.'/payments/PSP123/captures'
        && $request->hasHeader('x-api-key', 'eur-api-key')
        && $request->hasHeader('Idempotency-Key', 'capture-'.$transaction->id)
        && adyenRequestBody($request) == [
            'merchantAccount' => 'MerchantEUR',
            'amount' => ['value' => 19990, 'currency' => 'EUR'],
            'reference' => $transaction->id,
        ]);

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->persistentData['payment'])->toBe($resultData['payment'])
        ->and($result->persistentData['capture']['pspReference'])->toBe('CAPTURE123')
        ->and($result->persistentData['capture']['status'])->toBe('received');
});

it('captures a USD payment with the API key of the USD merchant account', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment('USD');

    fakeAdyen([ADYEN_TEST_URL.'/payments/PSP123/captures' => Http::response(['status' => 'received', 'pspReference' => 'CAPTURE123'], 201)]);

    (new AdyenWidgetGateway)->capture(adyenGatewayRequest($transaction), $init->persistentData, ['payment' => ['pspReference' => 'PSP123']]);

    Http::assertSent(fn (ClientRequest $request): bool => $request->hasHeader('x-api-key', 'usd-api-key')
        && adyenRequestBody($request)['merchantAccount'] === 'MerchantUSD'
        && adyenRequestBody($request)['amount'] == ['value' => 19990, 'currency' => 'USD']);
});

it('reports a failed capture and keeps the authorization data', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();
    $resultData = ['payment' => ['pspReference' => 'PSP123', 'resultCode' => 'Authorised']];

    fakeAdyen([ADYEN_TEST_URL.'/payments/PSP123/captures' => Http::response([
        'status' => 422,
        'errorCode' => '167',
        'message' => 'Original pspReference required for this operation',
        'errorType' => 'validation',
    ], 422)]);

    $result = (new AdyenWidgetGateway)->capture(adyenGatewayRequest($transaction), $init->persistentData, $resultData);

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->persistentData['payment'])->toBe($resultData['payment'])
        ->and($result->persistentData['capture'])->toBe(['error' => 'Original pspReference required for this operation']);
});

it('does not capture without the PSP reference of the authorized payment', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([]);

    $result = (new AdyenWidgetGateway)->capture(adyenGatewayRequest($transaction), $init->persistentData, ['reason' => 'missing_result']);

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->persistentData['reason'])->toBe('missing_result');

    Http::assertNothingSent();
});

it('reverses the authorized payment when the payment is aborted', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();
    $resultData = ['payment' => ['pspReference' => 'PSP123', 'resultCode' => 'Authorised']];

    fakeAdyen([ADYEN_TEST_URL.'/payments/PSP123/reversals' => Http::response([
        'merchantAccount' => 'MerchantEUR',
        'paymentPspReference' => 'PSP123',
        'pspReference' => 'REVERSAL123',
        'reference' => $transaction->id,
        'status' => 'received',
    ], 201)]);

    $result = (new AdyenWidgetGateway)->abort(adyenGatewayRequest($transaction), $init->persistentData, $resultData);

    Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
        && $request->url() === ADYEN_TEST_URL.'/payments/PSP123/reversals'
        && $request->hasHeader('Idempotency-Key', 'reversal-'.$transaction->id)
        && adyenRequestBody($request) == ['merchantAccount' => 'MerchantEUR', 'reference' => $transaction->id]);

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->persistentData['payment'])->toBe($resultData['payment'])
        ->and($result->persistentData['reversal']['pspReference'])->toBe('REVERSAL123');
});

it('reports a failed reversal', function (): void {
    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([ADYEN_TEST_URL.'/payments/PSP123/reversals' => Http::response('Internal Server Error', 500)]);

    $result = (new AdyenWidgetGateway)->abort(adyenGatewayRequest($transaction), $init->persistentData, ['payment' => ['pspReference' => 'PSP123']]);

    expect($result->isSuccessful)->toBeFalse()
        ->and($result->persistentData['reversal'])->toBe(['error' => 'Internal Server Error']);
});

it('logs every request and response of a payment like the other integrations', function (): void {
    $logs = captureHttpLogs();

    [$transaction, $init] = adyenGatewayPreparedPayment();

    fakeAdyen([
        ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction)),
        ADYEN_TEST_URL.'/payments/details' => Http::response([
            'resultCode' => 'Authorised',
            'pspReference' => 'PSP123',
            'merchantReference' => $transaction->id,
        ]),
        ADYEN_TEST_URL.'/payments/PSP123/captures' => Http::response(['status' => 'received', 'pspReference' => 'CAPTURE123'], 201),
        ADYEN_TEST_URL.'/payments/PSP123/reversals' => Http::response(['status' => 'received', 'pspReference' => 'REVERSAL123'], 201),
    ]);

    $request = adyenGatewayRequest($transaction, [
        'checkoutId' => 'co-123',
        'itineraryId' => 'it-456',
        'origin' => 'IBE',
        'lang' => 'de',
        'sessionResult' => 'SESSION-RESULT',
    ]);
    $this->app->instance('request', $request);

    $gateway = new AdyenWidgetGateway;
    $authorized = $gateway->authorize($request, $init->persistentData);
    $gateway->authorize(adyenGatewayRequest($transaction, ['redirectResult' => 'X6XtfGC3!Y']), $init->persistentData);
    $gateway->capture($request, $init->persistentData, $authorized->resultData);
    $gateway->abort($request, $init->persistentData, $authorized->resultData);

    $records = $logs->getRecords();
    $calls = array_map(fn ($record): string => $record->message.' '.$record->context['http']['method'].' '.parse_url((string) $record->context['http']['url'], PHP_URL_PATH), $records);

    expect($calls)->toBe([
        'checkout-to-adyen POST /v71/sessions',
        'adyen-to-checkout POST /v71/sessions',
        'checkout-to-adyen GET /v71/sessions/CS-SESSION-1',
        'adyen-to-checkout GET /v71/sessions/CS-SESSION-1',
        'checkout-to-adyen POST /v71/payments/details',
        'adyen-to-checkout POST /v71/payments/details',
        'checkout-to-adyen POST /v71/payments/PSP123/captures',
        'adyen-to-checkout POST /v71/payments/PSP123/captures',
        'checkout-to-adyen POST /v71/payments/PSP123/reversals',
        'adyen-to-checkout POST /v71/payments/PSP123/reversals',
    ]);

    foreach (array_slice($records, 2) as $record) {
        expect($record->context)->toMatchArray(['checkoutId' => 'co-123', 'itineraryId' => 'it-456', 'origin' => 'IBE', 'lang' => 'de']);
    }

    foreach ($records as $record) {
        if ($record->message === 'checkout-to-adyen') {
            expect($record->context['http']['headers']['x-api-key'])->toBe('[REDACTED]');
        }
    }

    expect(json_encode(array_map(fn ($record): array => $record->context, $records)))->not->toContain('eur-api-key');
});

it('builds the Nezasa transaction with the PSP reference of the payment', function (): void {
    $transaction = adyenGatewayTransaction();

    $payload = (new AdyenWidgetGateway)->makeNezasaTransactionPayload(
        adyenGatewayRequest($transaction),
        new CaptureResult(isSuccessful: true, persistentData: ['payment' => ['pspReference' => 'PSP123']])
    );

    expect($payload->externalRefId)->toBe('PSP123')
        ->and($payload->amount)->toEqual($transaction->price)
        ->and($payload->paymentMethod)->toBe(NezasaPaymentMethodEnum::Other)
        ->and($payload->status)->toBe(NezasaTransactionStatusEnum::Closed)
        ->and($payload->paymentMethodName)->toBe('Adyen');
});

it('prepares the Drop-in through the payment initiation and stores the payment data on the transaction', function (): void {
    Http::fake([ADYEN_TEST_URL.'/sessions' => Http::response(adyenGatewaySession(), 201)]);

    $checkout = adyenGatewayCheckout();
    $asset = (new PaymentInitiationHandler)->run(
        model: $checkout,
        price: new Price(199.9, 'EUR'),
        gateway: new AdyenWidgetGateway
    );

    $transaction = $checkout->transactions()->sole();

    expect($asset)->toBeInstanceOf(PaymentAsset::class)
        ->and($asset->scripts->implode(''))->toContain($transaction->id)
        ->and($transaction->gateway)->toBe('Adyen')
        ->and($transaction->status)->toBe(TransactionStatusEnum::Pending)
        ->and($transaction->prepare_data['merchantAccount'])->toBe('MerchantEUR')
        ->and($transaction->prepare_data['reference'])->toBe($transaction->id)
        ->and($transaction->prepare_data['sessionId'])->toBe('CS-SESSION-1');
});

it('authorizes, captures and records a rest payment on Nezasa through the payment result', function (): void {
    Config::set('checkout.nezasa.base_url', 'https://nezasa.example.test');

    [$transaction] = adyenGatewayPreparedPayment(checkout: adyenGatewayCheckout(restPayment: true));

    fakeAdyen([
        ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction)),
        ADYEN_TEST_URL.'/payments/PSP123/captures' => Http::response(['status' => 'received', 'pspReference' => 'CAPTURE123'], 201),
    ]);

    $mockClient = MockClient::global([
        CreatePaymentTransactionRequest::class => MockResponse::make(['id' => 'nezasa-transaction-1']),
    ]);

    $request = adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'sessionResult' => 'SESSION-RESULT']);
    $this->app->instance('request', $request);

    $output = resolve(RestPaymentCallBackHandler::class)->run($transaction, $request);
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatusEnum::Captured)
        ->and($transaction->result_data['payment']['pspReference'])->toBe('PSP123')
        ->and($transaction->result_data['capture']['pspReference'])->toBe('CAPTURE123')
        ->and($transaction->result_data['transaction_response'])->toBe(['id' => 'nezasa-transaction-1'])
        ->and($output->isPaymentSuccessful)->toBeTrue()
        ->and($output->bookingStatusEnum)->toBe(BookingStatusEnum::CompleteSuccess)
        ->and($mockClient->findResponseByRequest(CreatePaymentTransactionRequest::class)?->getPendingRequest()->body()?->all())
        ->toMatchArray([
            'externalRefId' => 'PSP123',
            'paymentMethod' => 'Other',
            'status' => 'Closed',
            'paymentMethodName' => 'Adyen',
        ]);
});

it('reverses the authorized payment through the payment result when the booking fails', function (): void {
    [$transaction] = adyenGatewayPreparedPayment();

    fakeAdyen([
        ADYEN_TEST_URL.'/sessions/*' => Http::response(adyenGatewaySessionResult($transaction)),
        ADYEN_TEST_URL.'/payments/PSP123/reversals' => Http::response(['status' => 'received', 'pspReference' => 'REVERSAL123'], 201),
    ]);

    $request = adyenGatewayRequest($transaction, ['sessionId' => 'CS-SESSION-1', 'sessionResult' => 'SESSION-RESULT']);
    $this->app->instance('request', $request);

    $handler = new DownPaymentCallBackHandler(
        new class extends BookItineraryAction
        {
            public function run(string $checkoutId): false|Response
            {
                return false;
            }
        },
        resolve(UpdateTransactionAction::class),
        resolve(FindBookingResultAction::class),
        resolve(CreateNezasaTransactionAction::class),
    );

    $output = $handler->run($transaction, $request);
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatusEnum::Aborted)
        ->and($transaction->result_data['reversal']['pspReference'])->toBe('REVERSAL123')
        ->and($output->isPaymentSuccessful)->toBeFalse();

    Http::assertNotSent(fn (ClientRequest $request): bool => str_contains($request->url(), '/captures'));
});
