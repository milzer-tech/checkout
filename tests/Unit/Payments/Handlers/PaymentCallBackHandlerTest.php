<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Mockery as m;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Integrations\Nezasa\Enums\BookingStateEnum;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\SynchronousBookingRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\UpdatePaymentTransactionRequest;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Nezasa\Checkout\Payments\Handlers\PaymentCallBackHandler;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

afterEach(function (): void {
    m::close();
});

// ---- Dummy callback gateway used for tests ----

class DummyCallbackGateway implements PaymentContract
{
    public function __construct(private ?PaymentResult $result = null) {}

    public static function isActive(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'dummy';
    }

    public function prepare(\Nezasa\Checkout\Payments\Dtos\PaymentPrepareData $data): \Nezasa\Checkout\Payments\Dtos\PaymentInit
    {
        throw new RuntimeException('Not used in callback tests');
    }

    public function makeNezasaTransactionPayload(\Nezasa\Checkout\Payments\Dtos\PaymentPrepareData $data, \Nezasa\Checkout\Payments\Dtos\PaymentInit $paymentInit): \Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload
    {
        throw new RuntimeException('Not used in callback tests');
    }

    public function verify(Request $request, array|\Nezasa\Checkout\Dtos\BaseDto $persistentData): PaymentResult
    {
        // If a specific result is injected, return it; otherwise derive from request
        if ($this->result !== null) {
            return $this->result;
        }

        $status = str($request->query('cb_status', 'failed'))->lower()->toString();

        return new PaymentResult(
            status: $status === 'succeeded' ? PaymentStatusEnum::Succeeded : PaymentStatusEnum::Failed,
            persistentData: ['source' => 'dummy', 'persistent' => $persistentData]
        );
    }

    public function output(\Nezasa\Checkout\Payments\Dtos\PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}

// ---- Helpers ----

function mockProviderForGateway(string $gatewayName, string $gatewayClass): void
{
    $provider = m::mock(GetPaymentProviderAction::class);
    $provider->shouldReceive('run')->andReturn([
        new PaymentOption(
            name: $gatewayName,
            encryptedGateway: Crypt::encrypt($gatewayName),
            encryptedClassName: Crypt::encrypt($gatewayClass)
        ),
    ]);

    app()->instance(GetPaymentProviderAction::class, $provider);
}

/**
 * Mock the Nezasa connector interactions for update/booking/retrieve used in PaymentCallBackHandler.
 *
 * @param  false|array<string,mixed>  $updateReturn
 */
function mockNezasaForCallback(false|array $updateReturn, bool $bookingOk, BookingStateEnum $state): void
{
    $retrieveBody = [
        'checkoutState' => $state->value,
        'prices' => [
            'discountedPackagePrice' => ['amount' => 0.0, 'currency' => 'EUR'],
            'packagePrice' => ['amount' => 0.0, 'currency' => 'EUR'],
            'totalPackagePrice' => ['amount' => 0.0, 'currency' => 'EUR'],
            'downPayment' => ['amount' => 0.0, 'currency' => 'EUR'],
            'externallyPaidCharges' => [
                'totalPrice' => ['amount' => 0.0, 'currency' => 'EUR'],
                'externallyPaidCharges' => [],
            ],
        ],
    ];

    $bookingStatus = $bookingOk ? 200 : 500;

    MockClient::global([
        RetrieveCheckoutRequest::class => MockResponse::make($retrieveBody, 200),
        SynchronousBookingRequest::class => MockResponse::make(['ok' => $bookingOk], $bookingStatus),
        UpdatePaymentTransactionRequest::class => MockResponse::make(['transaction' => $updateReturn], 200),
    ]);
}

it('returns stored output when transaction already has result_data and skips verify/update', function (): void {
    // Arrange checkout/transaction with existing result
    $checkout = Checkout::create([
        'checkout_id' => 'co-cb-1',
        'itinerary_id' => 'it-cb-1',
        'data' => [],
    ]);

    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'dummy',
        'amount' => '100.00',
        'currency' => 'EUR',
        'status' => PaymentStatusEnum::Succeeded,
        'result_data' => ['persisted' => true],
    ]);

    // Provider maps gateway name to our DummyCallbackGateway class
    mockProviderForGateway('dummy', DummyCallbackGateway::class);

    // Nezasa retrieve returns a successful booking state; booking should NOT be attempted in early return
    mockNezasaForCallback(updateReturn: ['ignored' => true], bookingOk: true, state: BookingStateEnum::BookingRequested);

    // Act
    $output = (new PaymentCallBackHandler)->run($tx, Request::create('/payment/result', 'GET'));

    // Assert
    expect($output)->toBeInstanceOf(PaymentOutput::class)
        ->and($output->gatewayName)->toBe('dummy')
        ->and($output->isNezasaBookingSuccessful)->toBeTrue()
        ->and($output->bookingReference)->toBe('it-cb-1')
        ->and($output->data)->toBe(['persisted' => true]);
});

it('verifies success, updates nezasa, stores result, books itinerary, and returns output', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-cb-2',
        'itinerary_id' => 'it-cb-2',
        'data' => [],
    ]);

    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'dummy',
        'amount' => '50.00',
        'currency' => 'CHF',
        'status' => PaymentStatusEnum::Started,
        'prepare_data' => ['foo' => 'bar'],
        'nezasa_transaction_ref_id' => 'REF-1',
    ]);

    mockProviderForGateway('dummy', DummyCallbackGateway::class);

    // Nezasa update returns an array, booking returns ok, retrieve shows successful booking state
    mockNezasaForCallback(updateReturn: ['trx' => 'X'], bookingOk: true, state: BookingStateEnum::BookingCompleted);

    // Since handler uses global request() inside, set the query there
    request()->query->set('cb_status', 'succeeded');
    $output = (new PaymentCallBackHandler)->run($tx, Request::create('/payment/result', 'GET'));

    $tx->refresh();

    expect($output)->toBeInstanceOf(PaymentOutput::class)
        ->and($output->isNezasaBookingSuccessful)->toBeTrue()
        ->and($tx->status->value)->toBe(PaymentStatusEnum::Succeeded->value)
        ->and($tx->result_data)->toBe(['source' => 'dummy', 'persistent' => ['foo' => 'bar']])
        ->and($tx->nezasa_transaction)->toBe(['trx' => 'X']);
});

it('verifies failure, updates nezasa as failed, booking not ok, and returns output with unsuccessful flag', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-cb-3',
        'itinerary_id' => 'it-cb-3',
        'data' => [],
    ]);

    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'dummy',
        'amount' => '10.00',
        'currency' => 'USD',
        'status' => PaymentStatusEnum::Started,
        'prepare_data' => ['alpha' => 'beta'],
        'nezasa_transaction_ref_id' => 'REF-2',
    ]);

    mockProviderForGateway('dummy', DummyCallbackGateway::class);

    // Update returns some payload, booking returns false, retrieve state not successful
    mockNezasaForCallback(updateReturn: ['status' => 'Failed'], bookingOk: false, state: BookingStateEnum::OptionInProgress);

    request()->query->set('cb_status', 'failed');
    $output = (new PaymentCallBackHandler)->run($tx, Request::create('/payment/result', 'GET'));

    $tx->refresh();

    expect($output)->toBeInstanceOf(PaymentOutput::class)
        ->and($output->isNezasaBookingSuccessful)->toBeFalse()
        ->and($tx->status->value)->toBe(PaymentStatusEnum::Failed->value);
});

it('handles nezasa update exception by preserving previous nezasa_transaction', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-cb-4',
        'itinerary_id' => 'it-cb-4',
        'data' => [],
    ]);

    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'dummy',
        'amount' => '20.00',
        'currency' => 'EUR',
        'status' => PaymentStatusEnum::Started,
        'prepare_data' => ['p' => 'q'],
        'nezasa_transaction' => ['old' => 'value'],
        'nezasa_transaction_ref_id' => 'REF-3',
    ]);

    mockProviderForGateway('dummy', DummyCallbackGateway::class);

    // Simulate update failure: our helper returns false from update->array(); booking ok true; retrieve successful
    mockNezasaForCallback(updateReturn: false, bookingOk: true, state: BookingStateEnum::BookingRequested);

    request()->query->set('cb_status', 'succeeded');
    $output = (new PaymentCallBackHandler)->run($tx, Request::create('/payment/result', 'GET'));

    $tx->refresh();

    expect($output)->toBeInstanceOf(PaymentOutput::class)
        ->and($tx->nezasa_transaction)->toBe(['old' => 'value']);
});
