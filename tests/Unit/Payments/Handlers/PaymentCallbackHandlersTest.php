<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Actions\Checkout\BookItineraryAction;
use Nezasa\Checkout\Actions\Checkout\FindBookingResultAction;
use Nezasa\Checkout\Actions\Payment\CreateNezasaTransactionAction;
use Nezasa\Checkout\Actions\Transaction\UpdateTransactionAction;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\BookingStatusEnum;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Payments\Handlers\DownPaymentCallBackHandler;
use Nezasa\Checkout\Payments\Handlers\RestPaymentCallBackHandler;
use Saloon\Http\Response;

final class CallbackGatewayForHandlerTest implements PaymentContract
{
    public static bool $authorizeSuccessful = true;

    public static bool $captureSuccessful = true;

    public static bool $abortSuccessful = true;

    public static function isActive(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'Callback Gateway';
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult): CreatePaymentTransactionPayload
    {
        throw new RuntimeException('Should not create Nezasa transaction in these branches.');
    }

    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        return new AuthorizationResult(
            isSuccessful: self::$authorizeSuccessful,
            resultData: ['authorized' => self::$authorizeSuccessful]
        );
    }

    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        return new CaptureResult(
            isSuccessful: self::$captureSuccessful,
            persistentData: [...$resultData, 'captured' => self::$captureSuccessful]
        );
    }

    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        return new AbortResult(
            isSuccessful: self::$abortSuccessful,
            persistentData: [...$resultData, 'aborted' => self::$abortSuccessful]
        );
    }
}

final class FailedBookingActionForHandlerTest extends BookItineraryAction
{
    public function run(string $checkoutId): false|Response
    {
        return false;
    }
}

function callbackHandlerTransaction(): Transaction
{
    $checkout = Checkout::factory()->create([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'APP',
        'lang' => 'en',
        'data' => [
            'status' => Checkout::buildSectionStatus(),
        ],
    ]);

    return Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => CallbackGatewayForHandlerTest::name(),
        'amount' => 100,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Pending,
        'prepare_data' => ['prepare' => true],
    ]);
}

beforeEach(function (): void {
    CallbackGatewayForHandlerTest::$authorizeSuccessful = true;
    CallbackGatewayForHandlerTest::$captureSuccessful = true;
    CallbackGatewayForHandlerTest::$abortSuccessful = true;

    Config::set('checkout.payment', [CallbackGatewayForHandlerTest::class]);
});

it('aborts down-payment authorization when itinerary booking fails', function (): void {
    $transaction = callbackHandlerTransaction();
    $handler = new DownPaymentCallBackHandler(
        new FailedBookingActionForHandlerTest,
        resolve(UpdateTransactionAction::class),
        resolve(FindBookingResultAction::class),
        resolve(CreateNezasaTransactionAction::class),
    );

    $output = $handler->run($transaction, Request::create('/callback'));
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatusEnum::Aborted)
        ->and($transaction->result_data)->toBe([
            'authorized' => true,
            'aborted' => true,
        ])
        ->and($output->bookingStatusEnum)->toBe(BookingStatusEnum::Unknown)
        ->and($output->isPaymentSuccessful)->toBeFalse();
});

it('marks rest-payment callback as capture failed when gateway capture fails', function (): void {
    CallbackGatewayForHandlerTest::$captureSuccessful = false;
    $transaction = callbackHandlerTransaction();
    $handler = new RestPaymentCallBackHandler(
        new FailedBookingActionForHandlerTest,
        resolve(UpdateTransactionAction::class),
        resolve(FindBookingResultAction::class),
        resolve(CreateNezasaTransactionAction::class),
    );

    $output = $handler->run($transaction, Request::create('/callback'));
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatusEnum::CaptureFailed)
        ->and($transaction->result_data)->toBe([
            'authorized' => true,
            'captured' => false,
        ])
        ->and($output->bookingStatusEnum)->toBe(BookingStatusEnum::Unknown)
        ->and($output->isPaymentSuccessful)->toBeFalse();
});

it('returns stored callback output without re-authorizing an already processed transaction', function (): void {
    CallbackGatewayForHandlerTest::$authorizeSuccessful = false;
    $transaction = callbackHandlerTransaction();
    $transaction->update([
        'status' => TransactionStatusEnum::Captured,
        'result_data' => [
            'nezasa_booking_summary' => [
                'components' => [
                    ['id' => 'hotel-1', 'isPlaceholder' => false, 'isBooked' => true, 'status' => 'BOOKED'],
                ],
            ],
        ],
    ]);
    $handler = new RestPaymentCallBackHandler(
        new FailedBookingActionForHandlerTest,
        resolve(UpdateTransactionAction::class),
        resolve(FindBookingResultAction::class),
        resolve(CreateNezasaTransactionAction::class),
    );

    $output = $handler->run($transaction, Request::create('/callback'));
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatusEnum::Captured)
        ->and($output->bookingStatusEnum)->toBe(BookingStatusEnum::CompleteSuccess)
        ->and($output->isPaymentSuccessful)->toBeTrue();
});
