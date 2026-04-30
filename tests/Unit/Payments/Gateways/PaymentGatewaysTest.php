<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Computop\Requests\ComputopCapturePaymentRequest;
use Nezasa\Checkout\Integrations\Computop\Requests\ComputopCreatePaymentRequest;
use Nezasa\Checkout\Integrations\Computop\Requests\ComputopReversePaymentRequest;
use Nezasa\Checkout\Integrations\Computop\Requests\GetComputopPaymentRequest;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaComplationRequest;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaPrepareRequest;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaStatusRequest;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Computop\ComputopGateway;
use Nezasa\Checkout\Payments\Gateways\Invoice\InvoiceGateway;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaWidgetGateway;
use Nezasa\Checkout\Payments\Gateways\Stripe\StripeGateway;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class TestableStripeGatewayForPaymentGatewayTest extends StripeGateway
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function exposeCustomizeSessionPayload(array $payload, Transaction $transaction): array
    {
        return $this->customizeSessionPayload($payload, $transaction);
    }
}

function paymentGatewayCheckout(array $data = []): Checkout
{
    return Checkout::factory()->create([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'APP',
        'lang' => 'en',
        'data' => array_replace_recursive([
            'contact' => [
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'gender' => GenderEnum::Female,
                'email' => 'jane@example.test',
                'country' => 'DE-Germany',
                'countryCode' => 'DE',
                'city' => 'Berlin',
                'postalCode' => '10115',
                'street1' => 'Main Street',
                'street2' => '42',
            ],
            'insurance' => null,
        ], $data),
    ]);
}

function paymentGatewayTransaction(?Checkout $checkout = null, string $gateway = 'test'): Transaction
{
    return Transaction::create([
        'checkout_id' => ($checkout ?? paymentGatewayCheckout())->id,
        'gateway' => $gateway,
        'amount' => 199.9,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Pending,
    ]);
}

function paymentGatewayPrepareData(Transaction $transaction): PaymentPrepareData
{
    return new PaymentPrepareData(
        transaction: $transaction,
        returnUrl: Uri::of('https://checkout.example.test/result'),
        cancelUrl: Uri::of('https://checkout.example.test/cancel'),
        contact: ContactInfoPayloadEntity::from($transaction->checkout->data['contact']),
        price: $transaction->price,
        checkoutId: $transaction->checkout->checkout_id,
        itineraryId: $transaction->checkout->itinerary_id,
        origin: $transaction->checkout->origin,
        lang: $transaction->checkout->lang,
    );
}

function requestWithTransaction(Transaction $transaction, array $query = []): Request
{
    $request = Request::create('/callback', 'GET', $query);
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

it('runs the full invoice gateway lifecycle without external calls', function (): void {
    Config::set('checkout.integrations.invoice.active', true);
    Config::set('checkout.integrations.invoice.name', 'Invoice');

    $transaction = paymentGatewayTransaction(gateway: 'Invoice');
    $gateway = new InvoiceGateway;
    $prepareData = paymentGatewayPrepareData($transaction);

    $init = $gateway->prepare($prepareData);
    $request = requestWithTransaction($transaction);
    $authorized = $gateway->authorize($request, $init->persistentData);
    $captured = $gateway->capture($request, $init->persistentData, $authorized->resultData);
    $aborted = $gateway->abort($request, $init->persistentData, $authorized->resultData);
    $payload = $gateway->makeNezasaTransactionPayload($request, $captured);

    expect(InvoiceGateway::isActive())->toBeTrue()
        ->and(InvoiceGateway::name())->toBe('Invoice')
        ->and($init->isAvailable)->toBeTrue()
        ->and($gateway->getRedirectUrl($init)->toStringable()->toString())->toBe($prepareData->returnUrl->toStringable()->toString())
        ->and($authorized->isSuccessful)->toBeTrue()
        ->and($captured->isSuccessful)->toBeTrue()
        ->and($aborted->isSuccessful)->toBeTrue()
        ->and($payload->externalRefId)->toBe((string) $transaction->id)
        ->and($payload->amount)->toEqual($transaction->price)
        ->and($payload->paymentMethod)->toBe(NezasaPaymentMethodEnum::BankTransfer)
        ->and($payload->status)->toBe(NezasaTransactionStatusEnum::Open);
});

it('prepares Oppwa widget assets, authorizes, captures, aborts, and builds Nezasa payload', function (): void {
    Config::set('checkout.integrations.oppwa.active', true);
    Config::set('checkout.integrations.oppwa.name', 'oppwa');
    Config::set('checkout.integrations.oppwa.base_url', 'https://oppwa.example.test');
    Config::set('checkout.integrations.oppwa.entity_id', 'entity-123');
    Config::set('checkout.integrations.oppwa.token', 'token-123');
    Config::set('checkout.integrations.oppwa.successful_result_code', '000.100.110');

    MockClient::global([
        OppwaPrepareRequest::class => MockResponse::make([
            'id' => 'checkout-id',
            'ndc' => 'checkout-id',
            'integrity' => 'sha384-test',
            'result' => ['code' => '000.200.100', 'description' => 'successfully created checkout'],
            'buildNumber' => 'build-1',
            'timestamp' => '2025-08-26T14:17:40+00:00',
        ]),
        OppwaStatusRequest::class => MockResponse::make([
            'id' => 'payment-id',
            'referencedId' => 'referenced-id',
            'amount' => '199.90',
            'currency' => 'EUR',
            'result' => ['code' => '000.100.110', 'description' => 'successful'],
        ]),
        OppwaComplationRequest::class => MockResponse::make([
            'id' => 'capture-id',
            'referencedId' => 'referenced-id',
            'result' => ['code' => '000.100.110', 'description' => 'successful'],
        ]),
    ]);

    $transaction = paymentGatewayTransaction(gateway: 'oppwa');
    $gateway = new OppwaWidgetGateway;
    $init = $gateway->prepare(paymentGatewayPrepareData($transaction));
    $assets = $gateway->getAssets($init);

    expect(OppwaWidgetGateway::isActive())->toBeTrue()
        ->and(OppwaWidgetGateway::name())->toBe('oppwa')
        ->and($init->isAvailable)->toBeTrue()
        ->and($init->persistentData['prepare'])->toBeInstanceOf(OppwaPrepareResponse::class)
        ->and($assets->isAvailable)->toBeTrue()
        ->and($assets->html)->toContain('paymentWidgets')
        ->and($assets->scripts->implode(''))->toContain('checkoutId=checkout-id');

    $request = requestWithTransaction($transaction, ['resourcePath' => 'v1/checkouts/checkout-id/payment']);
    $authorized = $gateway->authorize($request, $init->persistentData);
    $captured = $gateway->capture($request, $init->persistentData, $authorized->resultData);
    $aborted = $gateway->abort($request, $init->persistentData, $authorized->resultData);
    $payload = $gateway->makeNezasaTransactionPayload($request, $captured);

    expect($authorized->isSuccessful)->toBeTrue()
        ->and($captured->isSuccessful)->toBeTrue()
        ->and($aborted->isSuccessful)->toBeTrue()
        ->and($payload->externalRefId)->toBe('referenced-id')
        ->and($payload->paymentMethod)->toBe(NezasaPaymentMethodEnum::Other)
        ->and($payload->status)->toBe(NezasaTransactionStatusEnum::Closed)
        ->and($payload->paymentMethodName)->toBe('Oppwa');
});

it('runs Computop prepare, authorization, capture, abort, redirect, and Nezasa payload mapping', function (): void {
    Config::set('checkout.integrations.computop.active', true);
    Config::set('checkout.integrations.computop.name', 'Computop');
    Config::set('checkout.integrations.computop.base_url', 'https://computop.example.test');
    Config::set('checkout.integrations.computop.test_mode', true);
    Config::set('checkout.integrations.computop.username', 'user');
    Config::set('checkout.integrations.computop.password', 'pass');

    MockClient::global([
        ComputopCreatePaymentRequest::class => MockResponse::make([
            'paymentId' => 'pay-123',
            'transactionId' => 'transaction-123',
            'status' => 'OK',
            '_Links' => [
                'redirect' => ['href' => 'https://pay.example.test/redirect'],
            ],
        ], 201),
        GetComputopPaymentRequest::class => MockResponse::make([
            'paymentId' => 'pay-123',
            'transactionId' => 'transaction-123',
            'status' => 'CAPTURE_REQUEST',
        ]),
        ComputopCapturePaymentRequest::class => MockResponse::make([
            'paymentId' => 'pay-123',
            'transactionId' => 'transaction-123',
            'status' => 'OK',
        ]),
        ComputopReversePaymentRequest::class => MockResponse::make([
            'paymentId' => 'pay-123',
            'transactionId' => 'transaction-123',
            'status' => 'OK',
        ]),
    ]);

    $transaction = paymentGatewayTransaction(gateway: 'Computop');
    $gateway = new ComputopGateway;
    $init = $gateway->prepare(paymentGatewayPrepareData($transaction));
    $request = requestWithTransaction($transaction, ['PayID' => 'pay-123']);
    $authorized = $gateway->authorize($request, $init->persistentData);
    $captured = $gateway->capture($request, $init->persistentData, $authorized->resultData);
    $aborted = $gateway->abort($request, $init->persistentData, $authorized->resultData);
    $payload = $gateway->makeNezasaTransactionPayload($request, $captured);

    expect(ComputopGateway::isActive())->toBeTrue()
        ->and(ComputopGateway::name())->toBe('Computop')
        ->and($init->isAvailable)->toBeTrue()
        ->and($init->persistentData['paylaod']['order']['description'])->toBe(['Test:0000'])
        ->and($gateway->getRedirectUrl($init)->toStringable()->toString())->toBe('https://pay.example.test/redirect')
        ->and($authorized->isSuccessful)->toBeTrue()
        ->and($captured->isSuccessful)->toBeTrue()
        ->and($aborted->isSuccessful)->toBeTrue()
        ->and($payload->externalRefId)->toBe('pay-123')
        ->and($payload->paymentMethod)->toBe(NezasaPaymentMethodEnum::Other)
        ->and($payload->status)->toBe(NezasaTransactionStatusEnum::Closed)
        ->and($payload->paymentMethodName)->toBe('Computop');
});

it('builds Stripe Nezasa payload and adds vertical insurance submit text only when a quote is selected', function (): void {
    Config::set('checkout.integrations.stripe.active', true);
    Config::set('checkout.integrations.stripe.name', 'Credit Card');
    Config::set('checkout.insurance.vertical.active', true);

    $checkout = paymentGatewayCheckout([
        'insurance' => [
            'offer' => [
                'id' => 'quote-123',
                'title' => 'Vertical quote',
                'price' => ['amount' => 24.5, 'currency' => 'EUR'],
                'coverage' => [],
            ],
        ],
    ]);
    $transaction = paymentGatewayTransaction($checkout, 'Credit Card');
    $gateway = new TestableStripeGatewayForPaymentGatewayTest;
    $basePayload = ['mode' => 'payment'];
    $customized = $gateway->exposeCustomizeSessionPayload($basePayload, $transaction);

    $request = requestWithTransaction($transaction);
    $payload = $gateway->makeNezasaTransactionPayload(
        $request,
        new CaptureResult(true, ['payment_intent' => ['id' => 'pi_123']])
    );

    expect(StripeGateway::isActive())->toBeTrue()
        ->and(StripeGateway::name())->toBe('Credit Card')
        ->and($customized)->toHaveKey('custom_text.submit.message')
        ->and($payload->externalRefId)->toBe('pi_123')
        ->and($payload->paymentMethod)->toBe(NezasaPaymentMethodEnum::Other)
        ->and($payload->status)->toBe(NezasaTransactionStatusEnum::Closed)
        ->and($payload->paymentMethodName)->toBe('Stripe');

    $plainCheckout = paymentGatewayCheckout();
    $plainTransaction = paymentGatewayTransaction($plainCheckout, 'Credit Card');
    expect($gateway->exposeCustomizeSessionPayload($basePayload, $plainTransaction))->toBe($basePayload);
});
