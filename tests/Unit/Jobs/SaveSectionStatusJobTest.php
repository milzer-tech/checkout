<?php

use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Jobs\SaveSectionStatusJob;
use Nezasa\Checkout\Models\Checkout;

it('updates isCompleted and keeps existing isExpanded when not provided', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-123',
        'itinerary_id' => 'it-1',
        'data' => [
            'status' => [
                Section::Promo->value => [
                    'isCompleted' => false,
                    'isExpanded' => true,
                ],
            ],
        ],
    ]);

    SaveSectionStatusJob::make('co-123', Section::Promo, true)->handle();

    $fresh = $checkout->fresh();
    $data = $fresh->data?->toArray() ?? [];

    expect(data_get($data, 'status.'.Section::Promo->value.'.isCompleted'))->toBeTrue()
        ->and(data_get($data, 'status.'.Section::Promo->value.'.isExpanded'))->toBeTrue();
});

it('sets isExpanded when provided', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-456',
        'itinerary_id' => 'it-2',
        'data' => [],
    ]);

    (new SaveSectionStatusJob('co-456', Section::Contact, false, false))->handle();

    $fresh = $checkout->fresh();
    $data = $fresh->data?->toArray() ?? [];

    expect(data_get($data, 'status.'.Section::Contact->value.'.isCompleted'))->toBeFalse()
        ->and(data_get($data, 'status.'.Section::Contact->value.'.isExpanded'))->toBeFalse();
});

it('creates nested keys when data is initially null', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-789',
        'itinerary_id' => 'it-3',
    ]);

    (new SaveSectionStatusJob('co-789', Section::Traveller, true, null))->handle();

    $fresh = $checkout->fresh();
    $data = $fresh->data?->toArray() ?? [];

    expect(data_get($data, 'status.'.Section::Traveller->value.'.isCompleted'))->toBeTrue()
        ->and(data_get($data, 'status.'.Section::Traveller->value.'.isExpanded'))->toBeNull();
});

it('returns a uniqueId combining checkoutId and enum name', function (): void {
    $job = new SaveSectionStatusJob('xyz', Section::Summary, true);

    expect($job->uniqueId())->toBe('xyz-'.Section::Summary->name);
});

it('make() creates an equivalent instance', function (): void {
    $job = SaveSectionStatusJob::make('abc', Section::AdditionalService, false, true);

    expect($job)->toBeInstanceOf(SaveSectionStatusJob::class)
        ->and($job->checkoutId)->toBe('abc')
        ->and($job->section)->toBe(Section::AdditionalService)
        ->and($job->isCompleted)->toBeFalse()
        ->and($job->isExpanded)->toBeTrue();
});
