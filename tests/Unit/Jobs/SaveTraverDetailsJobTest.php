<?php

use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;
use Nezasa\Checkout\Models\Checkout;

it('updates an existing checkout data key', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-save-1',
        'itinerary_id' => 'it-1',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [
            'contact' => [
                'firstName' => 'Alice',
                'lastName' => 'Doe',
                'email' => 'alice@example.com',
            ],
        ],
    ]);

    (new SaveTraverDetailsJob('co-save-1', 'contact.email', 'newalice@example.com'))->handle();

    $fresh = $checkout->fresh();
    $data = $fresh->data?->toArray() ?? [];
    expect(data_get($data, 'contact.email'))->toBe('newalice@example.com');
});

it('updates arbitrary nested flag data on existing checkout', function (): void {
    Checkout::create([
        'checkout_id' => 'co-new',
        'itinerary_id' => 'it-new',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [],
    ]);

    (new SaveTraverDetailsJob('co-new', 'flags.nop', true))->handle();

    $checkout = Checkout::query()->where('checkout_id', 'co-new')->first();
    expect($checkout)->not->toBeNull();
    expect(data_get($checkout?->data?->toArray() ?? [], 'flags.nop'))->toBeTrue();
});

it('merges partial date updates instead of dropping siblings', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-date-1',
        'itinerary_id' => 'it-11',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [
            'paxInfo' => [[[
                'passportExpirationDate' => [
                    'day' => 15,
                    'month' => 7,
                    'year' => 2030,
                ],
            ]]],
        ],
    ]);

    (new SaveTraverDetailsJob('co-date-1', 'paxInfo.0.0.passportExpirationDate', [
        'month' => 8,
    ]))->handle();

    $savedDate = data_get($checkout->fresh()?->data?->toArray() ?? [], 'paxInfo.0.0.passportExpirationDate');
    expect($savedDate)->toBe([
        'day' => 15,
        'month' => 8,
        'year' => 2030,
    ]);
});

it('has expected public properties from constructor', function (): void {
    $job = new SaveTraverDetailsJob('co-xyz', 'contact.email', 'x');
    expect($job->checkoutId)->toBe('co-xyz')
        ->and($job->name)->toBe('contact.email')
        ->and($job->value)->toBe('x');
});
