<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Nezasa\Checkout\Exceptions\CheckoutInUseException;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Support\CheckoutOwnership;

function ownedCheckout(bool $restPayment = false): Checkout
{
    return Checkout::factory()->create([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'APP',
        'rest_payment' => $restPayment,
        'data' => [
            'contact' => ['firstName' => 'John', 'email' => 'john@example.com'],
            'paxInfo' => [[['firstName' => 'John', 'lastName' => 'Doe']]],
            'activityAnswers' => ['component-1' => ['question-1' => '180']],
            'numberOfPax' => 1,
        ],
    ]);
}

/**
 * Simulate the next request of a browser, holding the given key or none.
 */
function nextRequest(Checkout $checkout, ?string $key = null): CheckoutOwnership
{
    app()->forgetScopedInstances();
    Cookie::unqueue('checkout_owner_'.$checkout->id);
    request()->cookies->remove('checkout_owner_'.$checkout->id);

    if ($key !== null) {
        request()->cookies->set('checkout_owner_'.$checkout->id, $key);
    }

    return resolve(CheckoutOwnership::class);
}

function startPayment(Checkout $checkout, TransactionStatusEnum $status = TransactionStatusEnum::Pending): Transaction
{
    return Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Stripe',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => $status,
    ]);
}

function issuedKey(Checkout $checkout): string
{
    return Cookie::queued('checkout_owner_'.$checkout->id)->getValue();
}

beforeEach(function (): void {
    Config::set('checkout.customer_data.restore_always', false);
    Config::set('checkout.customer_data.ttl', 120);
    Config::set('checkout.payment_ttl', 60);
});

it('gives the claiming browser a key and stores only its hash', function (): void {
    $checkout = ownedCheckout();
    $ownership = nextRequest($checkout);

    $ownership->claim($checkout);

    $cookie = Cookie::queued('checkout_owner_'.$checkout->id);
    $checkout->refresh();

    expect($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getSameSite())->toBe('lax')
        ->and($checkout->data->get('owner')['token'])->toBe(hash('sha256', $cookie->getValue()))
        ->and($checkout->getOwner()->expiresAt->isFuture())->toBeTrue()
        ->and($ownership->isOwner($checkout))->toBeTrue();
});

it('starts a new owner with empty customer data and keeps the rest', function (): void {
    $checkout = ownedCheckout();

    nextRequest($checkout)->claim($checkout);

    $data = $checkout->refresh()->data;

    expect($data->get('contact'))->toBe([])
        ->and($data->get('paxInfo'))->toBe([])
        ->and($data->get('activityAnswers'))->toBe([])
        ->and($data->get('numberOfPax'))->toBe(1);
});

it('recognises the owner on the next request and keeps its data on a new claim', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    $key = issuedKey($checkout);
    $checkout->updateData(['contact' => ['firstName' => 'Jane']]);

    $ownership = nextRequest($checkout, $key);
    $ownership->claim($checkout);

    expect($ownership->isOwner($checkout))->toBeTrue()
        ->and($checkout->refresh()->data->get('contact'))->toBe(['firstName' => 'Jane']);
});

it('does not recognise a browser without the key or with a wrong one', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);

    expect(nextRequest($checkout)->isOwner($checkout))->toBeFalse()
        ->and(nextRequest($checkout, 'wrong-key')->isOwner($checkout))->toBeFalse();
});

it('does not recognise a checkout that never had an owner', function (): void {
    $checkout = ownedCheckout();

    expect(nextRequest($checkout, 'any-key')->isOwner($checkout))->toBeFalse();
});

it('does not recognise a malformed owner in the checkout data', function (): void {
    $checkout = ownedCheckout();
    $checkout->updateData(['owner' => ['token' => hash('sha256', 'any-key'), 'expiresAt' => 'not-a-date']]);

    expect($checkout->getOwner())->toBeNull()
        ->and(nextRequest($checkout, 'any-key')->isOwner($checkout))->toBeFalse();
});

it('lets another browser take over once the previous owner has been inactive for the configured minutes', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    $previousKey = issuedKey($checkout);

    $this->travel(121)->minutes();
    nextRequest($checkout)->claim($checkout);

    expect(nextRequest($checkout, $previousKey)->isOwner($checkout->refresh()))->toBeFalse();
});

it('expires the key after the configured minutes without any change', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    $key = issuedKey($checkout);

    $this->travel(119)->minutes();
    expect(nextRequest($checkout, $key)->isOwner($checkout))->toBeTrue();

    $this->travel(2)->minutes();
    expect(nextRequest($checkout, $key)->isOwner($checkout))->toBeFalse();
});

it('extends the key of the owner on every change', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    $key = issuedKey($checkout);

    $this->travel(100)->minutes();
    nextRequest($checkout, $key)->claim($checkout);

    expect(issuedKey($checkout))->toBe($key);

    $this->travel(100)->minutes();
    expect(nextRequest($checkout, $key)->isOwner($checkout->refresh()))->toBeTrue();
});

it('does not extend the key more than once a minute', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    $key = issuedKey($checkout);
    $expiresAt = $checkout->refresh()->getOwner()->expiresAt;

    $this->travel(30)->seconds();
    nextRequest($checkout, $key)->claim($checkout);

    expect($checkout->refresh()->getOwner()->expiresAt->equalTo($expiresAt))->toBeTrue()
        ->and(Cookie::queued('checkout_owner_'.$checkout->id))->toBeNull();
});

it('does not bind the rest payment to a browser', function (): void {
    $checkout = ownedCheckout(restPayment: true);
    $ownership = nextRequest($checkout);

    $ownership->claim($checkout);

    expect($ownership->isProtected($checkout))->toBeFalse()
        ->and($ownership->isOwner($checkout))->toBeTrue()
        ->and($checkout->refresh()->getOwner())->toBeNull()
        ->and($checkout->data->get('contact'))->toBe(['firstName' => 'John', 'email' => 'john@example.com']);
});

it('shows the customer data to everyone when restoring is always enabled', function (): void {
    Config::set('checkout.customer_data.restore_always', true);

    $checkout = ownedCheckout();
    $ownership = nextRequest($checkout);

    $ownership->claim($checkout);

    expect($ownership->isProtected($checkout))->toBeFalse()
        ->and($ownership->isOwner($checkout))->toBeTrue()
        ->and($checkout->refresh()->getOwner())->toBeNull()
        ->and($checkout->data->get('contact'))->toBe(['firstName' => 'John', 'email' => 'john@example.com']);
});

it('keeps other browsers out while the owner is active', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    $key = issuedKey($checkout);

    $this->travel(119)->minutes();

    expect(nextRequest($checkout)->isInUseElsewhere($checkout))->toBeTrue()
        ->and(nextRequest($checkout, 'wrong-key')->isInUseElsewhere($checkout))->toBeTrue()
        ->and(nextRequest($checkout, $key)->isInUseElsewhere($checkout))->toBeFalse();
});

it('does not let another browser claim the checkout while the owner is active', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    $checkout->updateData(['contact' => ['firstName' => 'Jane']]);

    expect(fn () => nextRequest($checkout)->claim($checkout))->toThrow(CheckoutInUseException::class)
        ->and($checkout->refresh()->data->get('contact'))->toBe(['firstName' => 'Jane']);
});

it('lets other browsers in once the owner has been inactive for the configured minutes', function (): void {
    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);

    $this->travel(121)->minutes();

    expect(nextRequest($checkout)->isInUseElsewhere($checkout))->toBeFalse();
});

it('keeps other browsers out while a payment is pending, even if the key of the owner expired', function (): void {
    Config::set('checkout.customer_data.ttl', 30);

    $checkout = ownedCheckout();
    nextRequest($checkout)->claim($checkout);
    startPayment($checkout);

    $this->travel(45)->minutes();
    expect(nextRequest($checkout)->isInUseElsewhere($checkout))->toBeTrue();

    $this->travel(16)->minutes();
    expect(nextRequest($checkout)->isInUseElsewhere($checkout))->toBeFalse();
});

it('does not keep other browsers out for a payment that is no longer pending', function (): void {
    $checkout = ownedCheckout();
    startPayment($checkout, TransactionStatusEnum::Canceled);

    expect(nextRequest($checkout)->isInUseElsewhere($checkout))->toBeFalse();
});

it('does not keep anyone out of the rest payment', function (): void {
    $checkout = ownedCheckout(restPayment: true);
    startPayment($checkout);

    expect(nextRequest($checkout)->isInUseElsewhere($checkout))->toBeFalse();
});
