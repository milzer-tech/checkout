<?php

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Mockery as m;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Enums\BookingStateEnum;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\SynchronousBookingRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\UpdatePaymentTransactionRequest;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\ReturnUrlHasInvalidQueryParamsForValidation;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentCallBack;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Nezasa\Checkout\Payments\Handlers\WidgetCallBackHandler;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('validates the given gateway in the callback handler', function (): void {
    $handler = new WidgetCallBackHandler;

    $transaction = new Transaction;
    $transaction->gateway = PaymentGatewayEnum::Oppwa;

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, []);

    $handler->run($transaction, m::mock(Request::class));
})->throws(InvalidArgumentException::class, 'The payment gateway is not supported.');

it('validates the given gateway implements the correct interface', function (): void {
    $handler = new WidgetCallBackHandler;

    $transaction = new Transaction;
    $transaction->gateway = PaymentGatewayEnum::Oppwa;

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, [
        PaymentGatewayEnum::Oppwa->value => stdClass::class,
    ]);

    $handler->run($transaction, m::mock(Request::class));
})->throws(InvalidArgumentException::class, 'The payment callback is not implemented correctly.');

it('aborts when the signature is invalid', function (): void {
    $handler = new WidgetCallBackHandler;

    $transaction = new Transaction;
    $transaction->gateway = PaymentGatewayEnum::Oppwa;
    $transaction->setRelation('checkout', new Checkout(['checkout_id' => 'chk_1', 'itinerary_id' => 'itn_1']));

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, [
        PaymentGatewayEnum::Oppwa->value => FakeCallback::class,
    ]);

    $request = m::mock(Request::class);
    $request->shouldReceive('hasValidSignatureWhileIgnoring')->once()->with([])->andReturnFalse();
    $request->shouldReceive('setUserResolver')->andReturnNull();

    app()->instance('request', $request);

    $handler->run($transaction, $request);
})->throws(HttpException::class, 'Invalid signature');

it('ignores added query params when validating signature', function (): void {
    MockClient::global([
        UpdatePaymentTransactionRequest::class => MockResponse::make([
            'transaction' => ['transactionRefId' => 'nez-1', 'status' => 'Closed'],
        ]),
        RetrieveCheckoutRequest::class => MockResponse::make([
            'checkoutState' => BookingStateEnum::BookingCompleted->value,
            'prices' => [
                'discountedPackagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'packagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'promoCode' => null,
            ],
        ]),
        SynchronousBookingRequest::class => MockResponse::make(['ok' => true]),
    ]);

    $handler = new WidgetCallBackHandler;

    $transaction = new Transaction;
    $transaction->gateway = PaymentGatewayEnum::Oppwa;
    $transaction->prepare_data = ['foo' => 'bar'];
    $transaction->nezasa_transaction_ref_id = 'nez-1';
    $transaction->setRelation('checkout', new Checkout(['checkout_id' => 'chk_1', 'itinerary_id' => 'itn_1']));

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, [
        PaymentGatewayEnum::Oppwa->value => FakeCallbackWithIgnoredParams::class,
    ]);

    $request = m::mock(Request::class);
    $request->shouldReceive('hasValidSignatureWhileIgnoring')->once()->with(['foo', 'bar'])->andReturnTrue();
    $request->shouldReceive('setUserResolver')->andReturnNull();

    app()->instance('request', $request);

    $transaction->setAttribute('updated_at', CarbonImmutable::parse('2024-01-01 00:00:00'));

    $output = $handler->run($transaction, $request);

    expect($output)
        ->toBeInstanceOf(PaymentOutput::class)
        ->and($output->isNezasaBookingSuccessful)->toBeTrue()
        ->and($output->bookingReference)->toBe('itn_1')
        ->and($output->gatewayName)->toBe(PaymentGatewayEnum::Oppwa);
});

it('returns stored output immediately when result data already exists', function (): void {
    MockClient::global([
        RetrieveCheckoutRequest::class => MockResponse::make([
            'checkoutState' => BookingStateEnum::BookingRequested->value,
            'prices' => [
                'discountedPackagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'packagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'promoCode' => null,
            ],
        ]),
    ]);

    $handler = new WidgetCallBackHandler;

    $transaction = new Transaction;
    $transaction->gateway = PaymentGatewayEnum::Oppwa;
    $transaction->status = PaymentStatusEnum::Succeeded;
    $transaction->result_data = ['saved' => true];
    $transaction->setRelation('checkout', new Checkout(['checkout_id' => 'chk_1', 'itinerary_id' => 'itn_1']));

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, [
        PaymentGatewayEnum::Oppwa->value => FakeCallback::class,
    ]);

    $output = $handler->run($transaction, m::mock(Request::class));

    expect($output)
        ->toBeInstanceOf(PaymentOutput::class)
        ->and($output->isNezasaBookingSuccessful)->toBeTrue()
        ->and($output->bookingReference)->toBe('itn_1')
        ->and($output->data)->toBe(['saved' => true]);
});

it('updates nezasa transaction, stores result and tries to book itinerary', function (): void {
    $stored = [];

    MockClient::global([
        UpdatePaymentTransactionRequest::class => MockResponse::make([
            'transaction' => ['transactionRefId' => 'nez-1', 'status' => 'Closed'],
        ]),
        RetrieveCheckoutRequest::class => MockResponse::make([
            'checkoutState' => BookingStateEnum::BookingCompleted->value,
            'prices' => [
                'discountedPackagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'packagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'promoCode' => null,
            ],
        ]),
        SynchronousBookingRequest::class => MockResponse::make(['ok' => true], 200),
    ]);

    $handler = new WidgetCallBackHandler;

    $transaction = m::mock(Transaction::class)->makePartial();
    $transaction->gateway = PaymentGatewayEnum::Oppwa;
    $transaction->prepare_data = ['foo' => 'bar'];
    $transaction->nezasa_transaction_ref_id = 'nez-1';
    $transaction->setRelation('checkout', new Checkout(['checkout_id' => 'chk_1', 'itinerary_id' => 'itn_1']));

    $transaction->shouldReceive('update')->once()->with(m::type('array'))->andReturnUsing(function (array $attrs) use (&$stored): true {
        $stored = $attrs;

        return true;
    });

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, [
        PaymentGatewayEnum::Oppwa->value => FakeCallback::class,
    ]);

    $request = m::mock(Request::class);
    $request->shouldReceive('hasValidSignatureWhileIgnoring')->andReturnTrue();
    $request->shouldReceive('setUserResolver')->andReturnNull();

    app()->instance('request', $request);

    $transaction->setAttribute('updated_at', CarbonImmutable::parse('2024-01-01 00:00:00'));

    $output = $handler->run($transaction, $request);

    expect($stored)
        ->toHaveKey('result_data')
        ->and($stored['status'])->toBe(PaymentStatusEnum::Succeeded->value)
        ->and($stored['nezasa_transaction']['status'] ?? null)->toBe('Closed');

    expect($output)
        ->toBeInstanceOf(PaymentOutput::class)
        ->and($output->isNezasaBookingSuccessful)->toBeTrue();
});

it('handles exceptions from nezasa update and booking gracefully', function (): void {
    $stored = [];

    // Make update request throw by not registering a mock and using a connector without a mock; we will mock the connector call to throw.
    // Instead, we simulate Saloon throwing by not mapping UpdatePaymentTransactionRequest and by intercepting via a fake connector is complex.
    // Simpler: We let NezasaConnector work with MockClient but respond with 500 to trigger exception path in createDtoFromResponse or use ok=false.
    MockClient::global([
        UpdatePaymentTransactionRequest::class => MockResponse::make([], 500),
        RetrieveCheckoutRequest::class => MockResponse::make([
            'checkoutState' => BookingStateEnum::BookingInProgress->value, // not successful
            'prices' => [
                'discountedPackagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'packagePrice' => ['amount' => 0, 'currency' => 'USD'],
                'promoCode' => null,
            ],
        ]),
        SynchronousBookingRequest::class => MockResponse::make([], 500),
    ]);

    $handler = new WidgetCallBackHandler;

    $transaction = m::mock(Transaction::class)->makePartial();
    $transaction->gateway = PaymentGatewayEnum::Oppwa;
    $transaction->prepare_data = ['foo' => 'bar'];
    $transaction->nezasa_transaction = ['existing' => true];
    $transaction->nezasa_transaction_ref_id = 'nez-1';
    $transaction->setRelation('checkout', new Checkout(['checkout_id' => 'chk_1', 'itinerary_id' => 'itn_1']));

    $transaction->shouldReceive('update')->once()->with(m::type('array'))->andReturnUsing(function (array $attrs) use (&$stored): true {
        $stored = $attrs;

        return true;
    });

    $reflection = new ReflectionClass($handler);
    $reflection->getProperty('implementations')->setValue($handler, [
        PaymentGatewayEnum::Oppwa->value => FakeCallbackFailed::class,
    ]);

    $request = m::mock(Request::class);
    $request->shouldReceive('hasValidSignatureWhileIgnoring')->andReturnTrue();
    $request->shouldReceive('setUserResolver')->andReturnNull();

    app()->instance('request', $request);

    $transaction->setAttribute('updated_at', CarbonImmutable::parse('2024-01-01 00:00:00'));

    $output = $handler->run($transaction, $request);

    expect($stored['nezasa_transaction'])
        ->toBe(['existing' => true])
        ->and($stored['status'])
        ->toBe(PaymentStatusEnum::Failed->value);

    expect($output->isNezasaBookingSuccessful)->toBeFalse();
});

class FakeCallback implements WidgetPaymentCallBack
{
    public function check(Request $request, $persistentData): PaymentResult
    {
        return new PaymentResult(
            status: PaymentStatusEnum::Succeeded,
            persistentData: ['checked' => true],
            gatewayName: PaymentGatewayEnum::Oppwa
        );
    }

    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        // pass-through
        return $output;
    }
}

final class FakeCallbackFailed implements WidgetPaymentCallBack
{
    public function check(Request $request, $persistentData): PaymentResult
    {
        return new PaymentResult(
            status: PaymentStatusEnum::Failed,
            persistentData: ['failed' => true],
            gatewayName: PaymentGatewayEnum::Oppwa
        );
    }

    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}

class FakeCallbackWithIgnoredParams implements ReturnUrlHasInvalidQueryParamsForValidation, WidgetPaymentCallBack
{
    public function check(Request $request, $persistentData): PaymentResult
    {
        return new PaymentResult(
            status: PaymentStatusEnum::Succeeded,
            persistentData: ['checked' => true],
            gatewayName: PaymentGatewayEnum::Oppwa
        );
    }

    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }

    public function addedParamsToReturnedUrl(Request $request): array
    {
        return ['foo', 'bar'];
    }
}
