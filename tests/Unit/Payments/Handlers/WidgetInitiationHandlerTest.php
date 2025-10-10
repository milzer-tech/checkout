<?php

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;
use Mockery as m;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\CreatePaymentTransactionRequest;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Contracts\AddQueryParamsToReturnUrl;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentInitiation;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Handlers\WidgetInitiationHandler;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('validates the given gateway in the handler', function (): void {
    $prepareData = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity,
        price: new Price(amount: 100.00, currency: 'USD'),
        checkoutId: 'chk_123456',
        itineraryId: 'itn_123456',
        origin: 'https://example.com',
    );

    $handler = new WidgetInitiationHandler;

    $handler->run(new Checkout, $prepareData, stdClass::class);
})->throws(InvalidArgumentException::class, 'The gateway does not implement PaymentInitiation.');

it('throws when service is not available', function (): void {
    $handler = new WidgetInitiationHandler;

    $method = new ReflectionMethod($handler, 'checkIfServiceAvailable');

    $init = new PaymentInit(false);

    $method->invoke($handler, $init);
})->throws(RuntimeException::class, 'Payment gateway is not available.');

it('creates base return url params without extra additions', function (): void {
    $handler = new WidgetInitiationHandler;

    $payment = new FakeGateway;

    $prepareData = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity,
        price: new Price(amount: 100.00, currency: 'USD'),
        checkoutId: 'chk_1',
        itineraryId: 'itn_1',
        origin: 'https://example.com',
        lang: 'en',
    );

    $init = $payment->prepare($prepareData);

    $method = new ReflectionMethod($handler, 'getReturnUrlParams');

    $params = $method->invoke($handler, $prepareData, $payment, $init);

    expect($params)->toMatchArray([
        'checkoutId' => 'chk_1',
        'itineraryId' => 'itn_1',
        'origin' => 'https://example.com',
        'lang' => 'en',
    ]);
});

it('merges extra query params when gateway implements AddQueryParamsToReturnUrl', function (): void {
    $handler = new WidgetInitiationHandler;

    $payment = new FakeGatewayWithParams;

    $prepareData = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity,
        price: new Price(amount: 100.00, currency: 'USD'),
        checkoutId: 'chk_2',
        itineraryId: 'itn_2',
        origin: 'https://example.com',
        lang: 'de',
    );

    $init = $payment->prepare($prepareData);

    $method = new ReflectionMethod($handler, 'getReturnUrlParams');

    $params = $method->invoke($handler, $prepareData, $payment, $init);

    expect($params)
        ->toMatchArray([
            'checkoutId' => 'chk_2',
            'itineraryId' => 'itn_2',
            'origin' => 'https://example.com',
            'lang' => 'de',
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
});

it('creates Nezasa transaction and returns the transaction array', function (): void {
    MockClient::global([
        CreatePaymentTransactionRequest::class => MockResponse::make([
            'transaction' => [
                'transactionRefId' => 'nez-tx-123',
                'status' => 'OPEN',
            ],
        ]),
    ]);

    $handler = new WidgetInitiationHandler;

    $method = new ReflectionMethod($handler, 'createNezasaTransaction');

    $payload = new CreatePaymentTransactionPayload(
        externalRefId: 'ext-1',
        amount: new Price(amount: 10.0, currency: 'USD'),
        paymentMethod: NezasaPaymentMethodEnum::CreditCard,
    );

    $result = $method->invoke($handler, 'chk_123', $payload);

    expect($result)->toMatchArray([
        'transactionRefId' => 'nez-tx-123',
        'status' => 'OPEN',
    ]);
});

it('persists a transaction with correct payload', function (): void {
    $handler = new WidgetInitiationHandler;

    $created = [];
    $model = m::mock(Checkout::class)->makePartial();
    $relation = m::mock(HasMany::class);
    $relation->shouldReceive('create')
        ->once()
        ->with(m::type('array'))
        ->andReturnUsing(function (array $attrs) use (&$created): \stdClass {
            $created = $attrs;

            return (object) $attrs;
        });
    $model->shouldReceive('transactions')->andReturn($relation);

    $init = new PaymentInit(true, ['persist' => 'yes']);

    $nezasa = ['transactionRefId' => 'nez-42', 'foo' => 'bar'];
    $price = new Price(amount: 55.5, currency: 'EUR');

    $method = new ReflectionMethod($handler, 'createTransaction');

    $method->invoke($handler, $model, $init, $nezasa, $price, 'Fake Gateway');

    expect($created)->toMatchArray([
        'gateway' => 'Fake Gateway',
        'prepare_data' => ['persist' => 'yes'],
        'status' => \Nezasa\Checkout\Payments\Enums\PaymentStatusEnum::Pending,
        'nezasa_transaction' => $nezasa,
        'nezasa_transaction_ref_id' => 'nez-42',
        'amount' => 55.5,
        'currency' => 'EUR',
    ]);
});

it('runs the handler end-to-end and returns assets with a signed return url', function (): void {
    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->withArgs(fn ($name, $expiration, $parameters): bool =>
            // basic sanity checks on parameters
            $name === 'payment-result'
            && isset($parameters['checkoutId'], $parameters['itineraryId'], $parameters['origin']))
        ->andReturn('https://example.com/return?sig=abc');

    MockClient::global([
        CreatePaymentTransactionRequest::class => MockResponse::make([
            'transaction' => [
                'transactionRefId' => 'nez-tx-777',
            ],
        ]),
    ]);

    $handler = new WidgetInitiationHandler;

    $prepareData = new PaymentPrepareData(
        contact: new ContactInfoPayloadEntity,
        price: new Price(amount: 100.00, currency: 'USD'),
        checkoutId: 'chk_999',
        itineraryId: 'itn_999',
        origin: 'https://origin.example',
        lang: 'en',
    );

    $created = [];
    $model = m::mock(Checkout::class)->makePartial();
    $relation = m::mock(HasMany::class);
    $relation->shouldReceive('create')->once()->with(m::type('array'))->andReturnUsing(function (array $attrs) use (&$created): \stdClass {
        $created = $attrs;

        return (object) $attrs;
    });
    $model->shouldReceive('transactions')->andReturn($relation);

    $asset = $handler->run($model, $prepareData, FakeGatewayWithParams::class);

    expect($asset)
        ->toBeInstanceOf(PaymentAsset::class)
        ->and($asset->html)
        ->toBe('https://example.com/return?sig=abc');

    expect($created['nezasa_transaction_ref_id'] ?? null)->toBe('nez-tx-777');
});

class FakeGateway implements WidgetPaymentInitiation
{
    public function __construct(
        private readonly bool $available = true,
        private readonly array $persistent = ['key' => 'value'],
    ) {}

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        return new PaymentInit($this->available, $this->persistent);
    }

    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset
    {
        return new PaymentAsset(true, html: $returnUrl);
    }

    public function getNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): CreatePaymentTransactionPayload
    {
        return new CreatePaymentTransactionPayload(
            externalRefId: 'ext-1',
            amount: new Price(amount: 10.0, currency: 'USD'),
            paymentMethod: NezasaPaymentMethodEnum::CreditCard,
        );
    }

    public static function name(): string
    {
        return 'Fake Gateway';
    }

    public static function description(): ?string
    {
        return null;
    }

    public static function isActive(): bool
    {
        return true;
    }
}

class FakeGatewayWithParams extends FakeGateway implements AddQueryParamsToReturnUrl
{
    public function addQueryParamsToReturnUrl(PaymentInit $paymentInit): array
    {
        return ['foo' => 'bar', 'baz' => 'qux'];
    }
}
