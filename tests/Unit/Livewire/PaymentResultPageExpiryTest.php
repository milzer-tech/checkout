<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Nezasa\Checkout\Dtos\Checkout\CheckoutOwnerDto;
use Nezasa\Checkout\Exceptions\CheckoutInUseException;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

final class ExpiredPaymentResultPageForTest extends PaymentResultPage
{
    public function mount(Request $request): void
    {
        $this->model = $this->transaction->checkout;
        $this->isExpired = true;
    }
}

function paymentResultPageForExpiryTest(bool $restPayment = false): PaymentResultPage
{
    $checkout = Checkout::factory()->create([
        'rest_payment' => $restPayment,
        'data' => ['owner' => CheckoutOwnerDto::issue('owner-key', 300)->toArray()],
    ]);

    $transaction = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Invoice',
        'amount' => 1000,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
    ]);

    $page = new PaymentResultPage;
    $page->transaction = $transaction;
    $page->model = $checkout;

    return $page;
}

function paymentResultHasExpired(PaymentResultPage $page): bool
{
    return (new ReflectionMethod(PaymentResultPage::class, 'hasExpired'))->invoke($page);
}

beforeEach(function (): void {
    Config::set('checkout.customer_data.restore_always', false);
    Config::set('checkout.payment_ttl', 60);
});

it('shows the payment result to the browser that owns the checkout', function (): void {
    $page = paymentResultPageForExpiryTest();
    request()->cookies->set('checkout_owner_'.$page->model->id, 'owner-key');

    $this->travel(3)->hours();

    expect(paymentResultHasExpired($page))->toBeFalse();
});

it('keeps another browser out of the payment result while the owner is active', function (): void {
    $page = paymentResultPageForExpiryTest();

    expect(fn (): bool => paymentResultHasExpired($page))->toThrow(CheckoutInUseException::class);
});

it('does not show the payment result to another browser once the owner is no longer active', function (): void {
    $page = paymentResultPageForExpiryTest();

    $this->travel(301)->minutes();

    expect(paymentResultHasExpired($page))->toBeTrue();

    $page->isExpired = true;

    expect($page->render()->name())->toBe('checkout::blades.payment-result-expired');
});

it('shows the rest payment result within the payment time after the transaction was created', function (): void {
    $page = paymentResultPageForExpiryTest(restPayment: true);

    $this->travel(59)->minutes();

    expect(paymentResultHasExpired($page))->toBeFalse();
});

it('limits the rest payment result by the transaction age only', function (): void {
    $page = paymentResultPageForExpiryTest(restPayment: true);
    request()->cookies->set('checkout_owner_'.$page->model->id, 'owner-key');

    $this->travel(61)->minutes();

    expect(paymentResultHasExpired($page))->toBeTrue();
});

it('renders the expired payment result without any booking details', function (): void {
    $page = paymentResultPageForExpiryTest();

    Livewire::test(ExpiredPaymentResultPageForTest::class, [
        'transaction' => $page->transaction,
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
    ])
        ->assertSee('This page has expired')
        ->assertDontSee('Traveller information');
});
