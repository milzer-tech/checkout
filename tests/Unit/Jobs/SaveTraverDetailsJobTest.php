<?php

use Illuminate\Support\Collection;
use Mockery as m;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\SaveTravellersDetailsPayload;
use Nezasa\Checkout\Integrations\Nezasa\Resources\CheckoutResource;
use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;
use Nezasa\Checkout\Models\Checkout;
use Saloon\Http\Response;

afterEach(function (): void {
    m::close();
});

it('updates checkout data and triggers saveTravelerDetails when contact and paxInfo complete', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-save-1',
        'itinerary_id' => 'it-1',
        'data' => [
            'numberOfPax' => 2,
            'contact' => [
                'firstName' => 'Alice',
                'lastName' => 'Doe',
                'email' => 'alice@example.com',
            ],
            'paxInfo' => [
                [
                    [
                        'firstName' => 'PaxA',
                        'lastName' => 'One',
                    ],
                    [
                        'firstName' => 'PaxB',
                        'lastName' => 'Two',
                    ],
                ],
            ],
        ],
    ]);

    $checkoutApi = m::mock(CheckoutResource::class);
    $checkoutApi->shouldReceive('saveTravelerDetails')
        ->once()
        ->withArgs(function ($checkoutId, $payload): bool {
            if ($checkoutId !== 'co-save-1') {
                return false;
            }

            if (! $payload instanceof SaveTravellersDetailsPayload) {
                return false;
            }

            expect($payload->contactInfo)->toBeInstanceOf(ContactInfoPayloadEntity::class)
                ->and($payload->contactInfo->firstName)->toBe('Alice')
                ->and($payload->contactInfo->email)->toBe('newalice@example.com');

            expect($payload->paxInfo)->toBeInstanceOf(Collection::class)
                ->and($payload->paxInfo)->toHaveCount(2);

            /** @var PaxInfoPayloadEntity $first */
            $first = $payload->paxInfo->get(0);
            /** @var PaxInfoPayloadEntity $second */
            $second = $payload->paxInfo->get(1);

            expect($first)->toBeInstanceOf(PaxInfoPayloadEntity::class)
                ->and($first->refId)->toBe('pax-0')
                ->and($first->firstName)->toBe('PaxA')
                ->and($second->refId)->toBe('pax-1')
                ->and($second->lastName)->toBe('Two');

            return true;
        })
        ->andReturn(m::mock(Response::class));

    $connector = m::mock(NezasaConnector::class);
    $connector->shouldReceive('checkout')->andReturn($checkoutApi);
    app()->instance(NezasaConnector::class, $connector);

    (new SaveTraverDetailsJob('co-save-1', 'contact.email', 'newalice@example.com'))->handle();

    $fresh = $checkout->fresh();
    $data = $fresh->data?->toArray() ?? [];
    expect(data_get($data, 'contact.email'))->toBe('newalice@example.com');
});

it('does not call saveTravelerDetails when contact missing', function (): void {
    Checkout::create([
        'checkout_id' => 'co-save-2',
        'itinerary_id' => 'it-2',
        'data' => [
            'numberOfPax' => 1,
            'paxInfo' => [[['firstName' => 'Solo']]],
        ],
    ]);

    $checkoutApi = m::mock(CheckoutResource::class);
    $checkoutApi->shouldReceive('saveTravelerDetails')->never();

    $connector = m::mock(NezasaConnector::class);
    $connector->shouldReceive('checkout')->andReturn($checkoutApi);
    app()->instance(NezasaConnector::class, $connector);

    (new SaveTraverDetailsJob('co-save-2', 'flags.nop', true))->handle();
});

it('does not call saveTravelerDetails when pax count mismatches numberOfPax', function (): void {
    Checkout::create([
        'checkout_id' => 'co-save-3',
        'itinerary_id' => 'it-3',
        'data' => [
            'numberOfPax' => 2,
            'contact' => ['firstName' => 'Bob', 'lastName' => 'Doe', 'email' => 'bob@example.com'],
            'paxInfo' => [
                [
                    ['firstName' => 'OnlyOne', 'lastName' => 'Pax'],
                ],
            ],
        ],
    ]);

    $checkoutApi = m::mock(CheckoutResource::class);
    $checkoutApi->shouldReceive('saveTravelerDetails')->never();

    $connector = m::mock(NezasaConnector::class);
    $connector->shouldReceive('checkout')->andReturn($checkoutApi);
    app()->instance(NezasaConnector::class, $connector);

    (new SaveTraverDetailsJob('co-save-3', 'contact.email', 'bobby@example.com'))->handle();
});

it('uniqueId combines checkoutId and name', function (): void {
    $job = new SaveTraverDetailsJob('co-xyz', 'contact.email', 'x');
    expect($job->uniqueId())->toBe(md5('co-xyz-contact.email'));
});
