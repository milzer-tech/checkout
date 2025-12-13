<?php

declare(strict_types=1);

use Illuminate\Support\Uri;
use Mockery as m;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Resources\PaymentTransactionResource;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Nezasa\Checkout\Payments\Handlers\PaymentInitiationHandler;
use Saloon\Http\Response;

afterEach(function (): void {
    m::close();
});

// ---- Test doubles (simple concrete gateways) ----

class DummyWidgetGateway implements WidgetPaymentContract
{
    public function __construct(
        private readonly PaymentInit $init,
        private readonly ?PaymentAsset $asset = null,
    ) {}

    public static function isActive(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'dummy-widget';
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        return $this->init;
    }

    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            externalRefId: 'ext-1',
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::CreditCard,
        );
    }

    public function getAssets(PaymentInit $paymentInit): PaymentAsset
    {
        return $this->asset ?? new PaymentAsset(true, html: '<div>ok</div>');
    }

    public function verify(Illuminate\Http\Request $request, array|Nezasa\Checkout\Dtos\BaseDto $persistentData): PaymentResult
    {
        return new PaymentResult(status: PaymentStatusEnum::Succeeded);
    }

    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}

class DummyRedirectGateway implements RedirectPaymentContract
{
    public function __construct(private readonly PaymentInit $init, private readonly Uri $uri) {}

    public static function isActive(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'dummy-redirect';
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        return $this->init;
    }

    public function getRedirectUrl(PaymentInit $init): Uri
    {
        return $this->uri;
    }

    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            externalRefId: 'ext-2',
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::Stripe,
        );
    }

    public function verify(Illuminate\Http\Request $request, array|Nezasa\Checkout\Dtos\BaseDto $persistentData): PaymentResult
    {
        return new PaymentResult(status: PaymentStatusEnum::Succeeded);
    }

    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}

class DummyUnsupportedGateway implements PaymentContract
{
    public static function isActive(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'unsupported';
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        return new PaymentInit(true, returnUrl: Uri::of('https://return.local'), persistentData: ['x' => 'y']);
    }

    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload
    {
        return new NezasaPayload(
            externalRefId: 'ext-3',
            amount: $data->price,
            paymentMethod: NezasaPaymentMethodEnum::CreditCard,
        );
    }

    public function verify(Illuminate\Http\Request $request, array|Nezasa\Checkout\Dtos\BaseDto $persistentData): PaymentResult
    {
        return new PaymentResult(status: PaymentStatusEnum::Succeeded);
    }

    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}

// ---- Helper to mock Nezasa create(payment) chain ----

function mockNezasaPaymentTransactionCreate(array $transaction): void
{
    /** @var NezasaConnector|m\MockInterface $connector */
    $connector = m::mock(NezasaConnector::class);
    /** @var PaymentTransactionResource|m\MockInterface $resource */
    $resource = m::mock(PaymentTransactionResource::class);
    /** @var Response|m\MockInterface $response */
    $response = m::mock(Response::class);

    $connector->shouldReceive('paymentTransaction')->andReturn($resource);
    $resource->shouldReceive('create')->andReturn($response);
    $response->shouldReceive('array')->with('transaction')->andReturn($transaction);

    app()->instance(NezasaConnector::class, $connector);
}

it('run() with WidgetPaymentContract returns PaymentAsset and updates transaction', function (): void {
    // Arrange request context
    request()->merge(['origin' => 'app', 'lang' => 'en']);

    $checkout = Checkout::create([
        'checkout_id' => 'co-h1',
        'itinerary_id' => 'it-h1',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [
            'contact' => [
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'email' => 'jane@example.com',
                'address' => [
                    'street1' => 'Main',
                    'city' => 'Zurich',
                    'postalCode' => '8000',
                    'country' => 'CH-CH',
                ],
            ],
        ],
    ]);

    // We don't assert specific ref id here; keep connector behavior out-of-scope
    mockNezasaPaymentTransactionCreate([
        'transactionRefId' => null,
        'foo' => 'bar',
    ]);

    $init = new PaymentInit(true, returnUrl: Uri::of('https://return.local'), persistentData: ['id' => 'abc']);
    $gateway = new DummyWidgetGateway($init, new PaymentAsset(true, html: '<div>w</div>'));

    $handler = new PaymentInitiationHandler;
    $result = $handler->run($checkout, new Price(123.45, 'EUR'), $gateway);

    // Assert result
    expect($result)->toBeInstanceOf(PaymentAsset::class);

    // Assert transaction created and updated
    $tx = $checkout->transactions()->latest('id')->first();
    expect($tx)->not->toBeNull()
        ->and($tx->gateway)->toBe(DummyWidgetGateway::name())
        ->and((float) $tx->amount)->toBe(123.45)
        ->and($tx->currency)->toBe('EUR')
        ->and($tx->status->value)->toBe(PaymentStatusEnum::Pending->value)
        ->and($tx->prepare_data)->toBe(['id' => 'abc']);
});

it('run() with RedirectPaymentContract returns Uri and updates transaction', function (): void {
    request()->merge(['origin' => 'ibe', 'lang' => 'de']);

    $checkout = Checkout::create([
        'checkout_id' => 'co-h2',
        'itinerary_id' => 'it-h2',
        'origin' => 'ibe',
        'lang' => 'de',
        'data' => [
            'contact' => [
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john@example.com',
                'address' => [
                    'street1' => 'Elm',
                    'city' => 'Berlin',
                    'postalCode' => '10115',
                    'country' => 'DE-DE',
                ],
            ],
        ],
    ]);

    mockNezasaPaymentTransactionCreate([
        'transactionRefId' => null,
    ]);

    $redirectUrl = Uri::of('https://example.test/pay');
    $init = new PaymentInit(true, returnUrl: Uri::of('https://return.local'), persistentData: ['session' => ['url' => $redirectUrl->toStringable()->toString()]]);
    $gateway = new DummyRedirectGateway($init, $redirectUrl);

    $handler = new PaymentInitiationHandler;
    $result = $handler->run($checkout, new Price(50.0, 'USD'), $gateway);

    expect($result)->toBeInstanceOf(Uri::class)
        ->and($result->toStringable()->toString())->toBe($redirectUrl->toStringable()->toString());

    $tx = $checkout->transactions()->latest('id')->first();
    expect($tx->status->value)->toBe(PaymentStatusEnum::Pending->value);
});

it('throws when gateway prepare is unavailable', function (): void {
    request()->merge(['origin' => 'app', 'lang' => 'en']);

    $checkout = Checkout::create([
        'checkout_id' => 'co-h3',
        'itinerary_id' => 'it-h3',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [
            'contact' => [
                'firstName' => 'A',
                'lastName' => 'B',
                'email' => 'a@b.c',
                'address' => [
                    'street1' => 'S', 'city' => 'C', 'postalCode' => 'P', 'country' => 'CH-CH',
                ],
            ],
        ],
    ]);

    // Do NOT mock Nezasa to ensure it is not called when unavailable
    $init = new PaymentInit(false, returnUrl: Uri::of('https://return.local'));
    $gateway = new DummyWidgetGateway($init);

    $handler = new PaymentInitiationHandler;

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Payment gateway is not available.');
    try {
        $handler->run($checkout, new Price(10.0, 'CHF'), $gateway);
    } finally {
        // A transaction is still created initially with Started status
        $tx = $checkout->transactions()->latest('id')->first();
        expect($tx)->not->toBeNull()
            ->and($tx->status->value)->toBe(PaymentStatusEnum::Started->value);
    }
});

it('throws for unsupported gateway implementation', function (): void {
    request()->merge(['origin' => 'app', 'lang' => 'en']);

    $checkout = Checkout::create([
        'checkout_id' => 'co-h4',
        'itinerary_id' => 'it-h4',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [
            'contact' => [
                'firstName' => 'A',
                'lastName' => 'B',
                'email' => 'a@b.c',
                'address' => [
                    'street1' => 'S', 'city' => 'C', 'postalCode' => 'P', 'country' => 'CH-CH',
                ],
            ],
        ],
    ]);

    mockNezasaPaymentTransactionCreate(['transactionRefId' => 'TRX-3']);

    $gateway = new DummyUnsupportedGateway;
    $handler = new PaymentInitiationHandler;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Payment gateway is not supported.');
    $handler->run($checkout, new Price(1.0, 'EUR'), $gateway);
});

it('makePaymentPrepareData builds correct payload', function (): void {
    request()->merge(['origin' => 'ibe', 'lang' => 'fr']);

    $checkout = Checkout::create([
        'checkout_id' => 'co-h5',
        'itinerary_id' => 'it-h5',
        'origin' => 'ibe',
        'lang' => 'fr',
        'data' => [
            'contact' => [
                'firstName' => 'Cathy',
                'lastName' => 'Stone',
                'email' => 'cathy@example.com',
                'address' => [
                    'street1' => 'Rue', 'city' => 'Paris', 'postalCode' => '75001', 'country' => 'FR-FR',
                ],
            ],
        ],
    ]);

    // Create a transaction similar to how handler does
    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'dummy',
        'status' => PaymentStatusEnum::Started,
        'amount' => 42.0,
        'currency' => 'EUR',
    ]);

    $dto = (new PaymentInitiationHandler)->makePaymentPrepareData($tx);

    $expectedUrl = route('payment-result', [
        'transaction' => $tx,
        'checkoutId' => 'co-h5',
        'itineraryId' => 'it-h5',
        'origin' => 'ibe',
        'lang' => 'fr',
    ]);

    expect($dto->checkoutId)->toBe('co-h5')
        ->and($dto->itineraryId)->toBe('it-h5')
        ->and($dto->origin)->toBe('ibe')
        ->and($dto->lang)->toBe('fr')
        ->and($dto->price->amount)->toBe(42.0)
        ->and($dto->price->currency)->toBe('EUR')
        ->and($dto->contact->email)->toBe('cathy@example.com')
        ->and($dto->returnUrl->toStringable()->toString())->toBe($expectedUrl);
});
