<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Actions\Checkout\FindBookingResultAction;
use Nezasa\Checkout\Actions\Checkout\FindCheckoutModelAction;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Operation\SaveSectionStatusAction;
use Nezasa\Checkout\Actions\Transaction\UpdateTransactionAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Exceptions\AlreadyPaidException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
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

class ActivePaymentProviderForActionTest implements PaymentContract
{
    public static function isActive(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'Active Test Gateway';
    }

    public static function isTokenized(): bool
    {
        return false;
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult): CreatePaymentTransactionPayload
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        throw new RuntimeException('Not used by this test.');
    }
}

final class InactivePaymentProviderForActionTest extends ActivePaymentProviderForActionTest
{
    public static function isActive(): bool
    {
        return false;
    }

    public static function name(): string
    {
        return 'Inactive Test Gateway';
    }
}

function checkoutWorkflowModel(array $attributes = []): Checkout
{
    return Checkout::factory()->create(array_replace_recursive([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'APP',
        'lang' => 'en',
        'data' => [
            'status' => Checkout::buildSectionStatus(),
        ],
    ], $attributes));
}

it('returns encrypted options only for active payment providers and decrypts them back', function (): void {
    Config::set('checkout.payment', [
        ActivePaymentProviderForActionTest::class,
        InactivePaymentProviderForActionTest::class,
    ]);

    $options = resolve(GetPaymentProviderAction::class)->run();

    expect($options)->toHaveCount(1)
        ->and($options[0]->name)->toBe('Active Test Gateway')
        ->and($options[0]->decryptGateway())->toBe('Active Test Gateway')
        ->and($options[0]->decryptClassName())->toBe(ActivePaymentProviderForActionTest::class);
});

it('rejects configured payment providers that do not implement the payment contract', function (): void {
    Config::set('checkout.payment', [stdClass::class]);

    expect(fn () => resolve(GetPaymentProviderAction::class)->run())
        ->toThrow(InvalidArgumentException::class, 'is not an instance of WidgetPaymentInitiation');
});

it('classifies booking summaries while ignoring placeholder components', function (): void {
    $action = resolve(FindBookingResultAction::class);

    expect($action->run([
        'components' => [
            ['id' => 'hotel', 'isPlaceholder' => false, 'isBooked' => true],
            ['id' => 'placeholder', 'isPlaceholder' => true, 'isBooked' => false],
        ],
    ]))->toBe(BookingStatusEnum::CompleteSuccess)
        ->and($action->run([
            'components' => [
                ['id' => 'hotel', 'isPlaceholder' => false, 'isBooked' => false],
            ],
        ]))->toBe(BookingStatusEnum::CompleteFailed)
        ->and($action->run([
            'components' => [
                ['id' => 'hotel', 'isPlaceholder' => false, 'isBooked' => true],
                ['id' => 'activity', 'isPlaceholder' => false, 'isBooked' => false],
            ],
        ]))->toBe(BookingStatusEnum::PartialFailure)
        ->and($action->run(null))->toBe(BookingStatusEnum::Unknown);
});

it('finds the matching checkout by rest-payment flag and blocks already captured checkouts', function (): void {
    $checkout = checkoutWorkflowModel([
        'checkout_id' => 'co-find',
        'itinerary_id' => 'it-find',
        'rest_payment' => false,
    ]);
    $restCheckout = checkoutWorkflowModel([
        'checkout_id' => 'co-find',
        'itinerary_id' => 'it-find',
        'rest_payment' => true,
    ]);

    $action = resolve(FindCheckoutModelAction::class);

    expect($action->run(new CheckoutParamsDto('co-find', 'it-find', 'APP', 'en', false))->is($checkout))->toBeTrue()
        ->and($action->run(new CheckoutParamsDto('co-find', 'it-find', 'APP', 'en', true))->is($restCheckout))->toBeTrue();

    Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Invoice',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
    ]);

    expect(fn () => $action->run(new CheckoutParamsDto('co-find', 'it-find', 'APP', 'en', false)))
        ->toThrow(AlreadyPaidException::class);
});

it('persists section status and transaction updates through their workflow actions', function (): void {
    $checkout = checkoutWorkflowModel();

    resolve(SaveSectionStatusAction::class)->run($checkout, Section::Promo, isCompleted: true, isExpanded: false);
    $checkout->refresh();

    expect(data_get($checkout->data, 'status.promo.isCompleted'))->toBeTrue()
        ->and(data_get($checkout->data, 'status.promo.isExpanded'))->toBeFalse();

    $transaction = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Invoice',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Pending,
    ]);

    $updated = resolve(UpdateTransactionAction::class)->run($transaction->id, [
        'status' => TransactionStatusEnum::Authorized,
        'result_data' => ['authorized' => true],
    ]);

    $transaction->refresh();

    expect($updated)->toBeTrue()
        ->and($transaction->status)->toBe(TransactionStatusEnum::Authorized)
        ->and($transaction->result_data)->toBe(['authorized' => true])
        ->and($transaction->price)->toEqual(new Price(100.0, 'EUR'));
});
