<?php

use Illuminate\Support\Collection;
use Nezasa\Checkout\Actions\Checkout\InitializeCheckoutDataAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Exceptions\AlreadyPaidException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RoomAllocationResponseEntity;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

it('creates a new Checkout with initial data and computed pax count when none exists', function (): void {
    $params = new CheckoutParamsDto('co-123', 'it-456', 'app', 'en');

    $allocatedPax = new PaxAllocationResponseEntity(
        rooms: new Collection([
            new RoomAllocationResponseEntity(adults: 2, childAges: [5, 7]),
            new RoomAllocationResponseEntity(adults: 1, childAges: []),
        ])
    );

    $action = new InitializeCheckoutDataAction;

    $model = $action->run($params, $allocatedPax);

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

it('throws AlreadyPaidException when a checkout with a succeeded transaction already exists', function (): void {
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
        'status' => TransactionStatusEnum::Succeeded->value,
    ]);

    $allocatedPax = new PaxAllocationResponseEntity(rooms: new Collection);

    $action = new InitializeCheckoutDataAction;

    $this->expectException(AlreadyPaidException::class);

    $action->run($params, $allocatedPax);
});
