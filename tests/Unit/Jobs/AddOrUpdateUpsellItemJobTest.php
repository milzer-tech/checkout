<?php

use Illuminate\Support\Collection;
use Mockery as m;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddOrRemoveUpsellItemsPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\UpsellItemOfferPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Resources\CheckoutResource;
use Nezasa\Checkout\Jobs\AddOrUpdateUpsellItemJob;
use Nezasa\Checkout\Models\Checkout;

// Ensure Mockery aliases do not leak between tests when the whole test suite runs
afterEach(function (): void {
    m::close();
});

it('calls connector with correct payload and marks summary completed on success', function (): void {
    // Ensure a checkout exists for SaveSectionStatusJob to update
    Checkout::create([
        'checkout_id' => 'co-1',
        'itinerary_id' => 'it-1',
        'data' => [],
    ]);

    // Mock NezasaConnector resolved via the container
    $checkoutApi = m::mock(CheckoutResource::class);
    $checkoutApi->shouldReceive('addOrUpdateUpsellItem')
        ->once()
        ->withArgs(function ($checkoutId, $payload): bool {
            if ($checkoutId !== 'co-1') {
                return false;
            }

            if (! $payload instanceof AddOrRemoveUpsellItemsPayload) {
                return false;
            }

            expect($payload->selection)->toBeInstanceOf(Collection::class)
                ->and($payload->selection)->toHaveCount(1);

            /** @var UpsellItemOfferPayloadEntity $entity */
            $entity = $payload->selection->first();

            expect($entity)->toBeInstanceOf(UpsellItemOfferPayloadEntity::class)
                ->and($entity->offerId)->toBe('offer-1')
                ->and($entity->serviceCategoryRefId)->toBe('svc-1')
                ->and($entity->quantity)->toBe(2);

            return true;
        });

    $connector = m::mock(NezasaConnector::class);
    $connector->shouldReceive('checkout')->andReturn($checkoutApi);
    app()->instance(NezasaConnector::class, $connector);

    // Act
    (new AddOrUpdateUpsellItemJob('co-1', 'offer-1', 'svc-1', 2))->handle();

    // Assert SaveSectionStatusJob side-effect
    $fresh = Checkout::whereCheckoutId('co-1')->first();
    $data = $fresh->data?->toArray() ?? [];
    expect(data_get($data, 'status.'.Section::Summary->value.'.isCompleted'))->toBeTrue();
});

it('sends null serviceCategoryRefId and quantity when quantity is zero', function (): void {
    // Ensure a checkout exists for SaveSectionStatusJob to update
    Checkout::create([
        'checkout_id' => 'co-2',
        'itinerary_id' => 'it-2',
        'data' => [],
    ]);

    $checkoutApi = m::mock(CheckoutResource::class);
    $checkoutApi->shouldReceive('addOrUpdateUpsellItem')
        ->once()
        ->withArgs(function ($checkoutId, $payload): bool {
            if (! $payload instanceof AddOrRemoveUpsellItemsPayload) {
                return false;
            }

            /** @var UpsellItemOfferPayloadEntity $entity */
            $entity = $payload->selection->first();

            expect($entity->serviceCategoryRefId)->toBeNull()
                ->and($entity->quantity)->toBeNull();

            return true;
        });

    $connector = m::mock(NezasaConnector::class);
    $connector->shouldReceive('checkout')->andReturn($checkoutApi);
    app()->instance(NezasaConnector::class, $connector);

    // Act
    (new AddOrUpdateUpsellItemJob('co-2', 'offer-2', 'svc-2', 0))->handle();

    // Assert SaveSectionStatusJob side-effect
    $fresh = Checkout::whereCheckoutId('co-2')->first();
    $data = $fresh->data?->toArray() ?? [];
    expect(data_get($data, 'status.'.Section::Summary->value.'.isCompleted'))->toBeTrue();
});

it('failed handler also marks summary completed', function (): void {
    // Ensure checkout exists
    Checkout::create([
        'checkout_id' => 'co-3',
        'itinerary_id' => 'it-3',
        'data' => [],
    ]);

    // Act
    (new AddOrUpdateUpsellItemJob('co-3', 'offer-3', 'svc-3', 1))->failed(null);

    // Assert SaveSectionStatusJob side-effect
    $fresh = Checkout::whereCheckoutId('co-3')->first();
    $data = $fresh->data?->toArray() ?? [];
    expect(data_get($data, 'status.'.Section::Summary->value.'.isCompleted'))->toBeTrue();
});

it('uniqueId is md5 of concatenated properties', function (): void {
    $job = new AddOrUpdateUpsellItemJob('A', 'B', 'C', 5);

    expect($job->uniqueId())->toBe(md5('A'.'B'.'C'.'5'));
});
