<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Nezasa\Checkout\Actions\Checkout\InitializeCheckoutDataAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutOwnerDto;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RoomAllocationResponseEntity;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Support\CheckoutOwnership;

it('creates a new Checkout with initial data and computed pax count when none exists', function (): void {
    $params = new CheckoutParamsDto('co-123', 'it-456', 'app', 'en');

    $allocatedPax = new PaxAllocationResponseEntity(
        rooms: new Collection([
            new RoomAllocationResponseEntity(adults: 2, childAges: [5, 7]),
            new RoomAllocationResponseEntity(adults: 1, childAges: []),
        ])
    );

    $action = resolve(InitializeCheckoutDataAction::class);

    $model = $action->run(null, $params, $allocatedPax);

    $persisted = Checkout::whereCheckoutId($params->checkoutId)->whereItineraryId($params->itineraryId)->first();
    expect($persisted)->not->toBeNull();
    expect($model->id)->toBe($persisted->id);

    /** @var Collection $data */
    $data = $persisted->data;
    expect($data->get('numberOfPax'))->toBe(5);

    // Basic structure checks for status flags
    $status = $data->get('status');
    expect($status)->toBeArray();

    expect($status[Section::Contact->value]['isExpanded'])->toBeTrue();
    expect($status[Section::Contact->value]['isCompleted'])->toBeFalse();

    expect($status[Section::Traveller->value]['isExpanded'])->toBeFalse();
    expect($status[Section::Traveller->value]['isCompleted'])->toBeFalse();

    expect($status[Section::Promo->value]['isExpanded'])->toBeFalse();
    expect($status[Section::Promo->value]['isCompleted'])->toBeFalse();

    expect($status[Section::AdditionalService->value]['isExpanded'])->toBeFalse();
    expect($status[Section::AdditionalService->value]['isCompleted'])->toBeFalse();

    expect($status[Section::Summary->value]['isExpanded'])->toBeTrue();
    expect($status[Section::Summary->value]['isCompleted'])->toBeTrue();

    expect($status[Section::PaymentOptions->value]['isExpanded'])->toBeFalse();
    expect($status[Section::PaymentOptions->value]['isCompleted'])->toBeFalse();
});

it('returns existing checkout when a succeeded transaction already exists', function (): void {
    $params = new CheckoutParamsDto('co-123', 'it-456', 'app', 'en');

    // Seed an existing checkout
    $checkout = Checkout::create([
        'checkout_id' => $params->checkoutId,
        'itinerary_id' => $params->itineraryId,
        'origin' => $params->origin,
    ]);

    // Attach a succeeded transaction
    Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'oppwa',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
    ]);

    $allocatedPax = new PaxAllocationResponseEntity(rooms: new Collection);

    $action = resolve(InitializeCheckoutDataAction::class);

    $result = $action->run($checkout, $params, $allocatedPax);

    expect($result->is($checkout))->toBeTrue();
});

function seedVisitedCheckout(CheckoutParamsDto $params): Checkout
{
    return Checkout::create([
        'checkout_id' => $params->checkoutId,
        'itinerary_id' => $params->itineraryId,
        'origin' => $params->origin,
        'data' => [
            'contact' => ['firstName' => 'John', 'email' => 'john@example.com'],
            'paxInfo' => [[['firstName' => 'John', 'lastName' => 'Doe']]],
            'numberOfPax' => 1,
            'status' => Checkout::buildSectionStatus(),
        ],
    ]);
}

it('makes the browser that creates the checkout its owner', function (): void {
    $params = new CheckoutParamsDto('co-123', 'it-456', 'app', 'en');

    $checkout = resolve(InitializeCheckoutDataAction::class)
        ->run(null, $params, new PaxAllocationResponseEntity(rooms: new Collection));

    $cookie = Cookie::queued('checkout_owner_'.$checkout->id);

    expect($cookie)->not->toBeNull()
        ->and($checkout->refresh()->getOwner()->isHeldBy($cookie->getValue()))->toBeTrue()
        ->and(resolve(CheckoutOwnership::class)->isOwner($checkout))->toBeTrue();
});

it('makes a browser the new owner with empty customer data when nobody else uses the checkout', function (): void {
    $params = new CheckoutParamsDto('co-123', 'it-456', 'app', 'en');
    $checkout = seedVisitedCheckout($params);

    resolve(InitializeCheckoutDataAction::class)
        ->run($checkout, $params, new PaxAllocationResponseEntity(rooms: new Collection));

    $data = $checkout->refresh()->data;

    expect(resolve(CheckoutOwnership::class)->isOwner($checkout))->toBeTrue()
        ->and($data->get('contact'))->toBe([])
        ->and($data->get('paxInfo'))->toBe([])
        ->and($data->get('numberOfPax'))->toBe(1);
});

it('extends the key of the owner when the checkout is revisited', function (): void {
    $params = new CheckoutParamsDto('co-123', 'it-456', 'app', 'en');
    $checkout = seedVisitedCheckout($params);
    $checkout->updateData(['owner' => CheckoutOwnerDto::issue('owner-key', 5)->toArray()]);
    request()->cookies->set('checkout_owner_'.$checkout->id, 'owner-key');

    resolve(InitializeCheckoutDataAction::class)
        ->run($checkout, $params, new PaxAllocationResponseEntity(rooms: new Collection));

    expect($checkout->refresh()->getOwner()->expiresAt->greaterThan(now()->addMinutes(100)))->toBeTrue()
        ->and($checkout->data->get('contact'))->toBe(['firstName' => 'John', 'email' => 'john@example.com'])
        ->and(Cookie::queued('checkout_owner_'.$checkout->id)->getValue())->toBe('owner-key');
});

it('does not take over the owner of the down payment for the rest payment', function (): void {
    $downParams = new CheckoutParamsDto('co-123', 'it-456', 'app', 'en');
    $downCheckout = seedVisitedCheckout($downParams);
    $downCheckout->updateData(['owner' => CheckoutOwnerDto::issue('owner-key', 60)->toArray()]);

    $restCheckout = resolve(InitializeCheckoutDataAction::class)->run(
        null,
        new CheckoutParamsDto('co-123', 'it-456', 'app', 'en', true),
        new PaxAllocationResponseEntity(rooms: new Collection)
    );

    expect($restCheckout->refresh()->data->has('owner'))->toBeFalse()
        ->and($restCheckout->data->get('contact'))->toBe(['firstName' => 'John', 'email' => 'john@example.com']);
});
