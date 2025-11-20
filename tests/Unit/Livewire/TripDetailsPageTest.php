<?php

use Mockery as m;
use Nezasa\Checkout\Actions\Checkout\InitializeCheckoutDataAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Livewire\TripDetailsPage;
use Nezasa\Checkout\Models\Checkout;

beforeEach(function (): void {
    fakeInitialNezasaCalls();
});

afterEach(function (): void {
    m::close();
});

it('mount() initializes result, model and itinerary via injected actions', function (): void {
    $realCall = new CallTripDetailsAction;
    $responses = $realCall->run('it-td-1', 'co-td-1');

    $model = Checkout::create([
        'checkout_id' => 'co-td-1',
        'itinerary_id' => 'it-td-1',
        'data' => [],
    ]);

    $realSummarize = new SummarizeItineraryAction;
    $summary = $realSummarize->run(
        itineraryResponse: $responses->itinerary,
        checkoutResponse: $responses->checkout,
        addedRentalCarResponse: $responses->addedRentalCars,
        addedUpsellItemsResponse: collect($responses->addedUpsellItems),
    );

    $callMock = m::mock(CallTripDetailsAction::class);
    $callMock->shouldReceive('run')
        ->once()
        ->with('it-td-1', 'co-td-1')
        ->andReturn($responses);

    $initMock = m::mock(InitializeCheckoutDataAction::class);
    $initMock->shouldReceive('run')
        ->once()
        ->withArgs(fn (string $checkoutId, string $itineraryId, $allocatedPax): bool => $checkoutId === 'co-td-1' && $itineraryId === 'it-td-1' && $allocatedPax !== null)
        ->andReturn($model);

    $sumMock = m::mock(SummarizeItineraryAction::class);
    $sumMock->shouldReceive('run')
        ->once()
        ->withArgs(fn (...$args): bool => $args[0] === $responses->itinerary
            && $args[1] === $responses->checkout
            && $args[2] === $responses->addedRentalCars
            && (is_iterable($args[3])))
        ->andReturn($summary);

    $component = new TripDetailsPage;
    $component->checkoutId = 'co-td-1';
    $component->itineraryId = 'it-td-1';
    $component->origin = 'app';
    $component->lang = 'en';

    $component->mount($callMock, $sumMock, $initMock);

    expect($component->result)->toBe($responses)
        ->and($component->model->is($model))->toBeTrue()
        ->and($component->itinerary)->toBeInstanceOf(ItinerarySummary::class)
        ->and($component->itinerary->title)->toBe($summary->title);
});

it('render() returns the trip details blade view', function (): void {
    $responses = (new CallTripDetailsAction)->run('it-td-2', 'co-td-2');
    $model = Checkout::create(['checkout_id' => 'co-td-2', 'itinerary_id' => 'it-td-2', 'data' => []]);

    $callMock = m::mock(CallTripDetailsAction::class);
    $callMock->shouldReceive('run')->andReturn($responses);

    $initMock = m::mock(InitializeCheckoutDataAction::class);
    $initMock->shouldReceive('run')->andReturn($model);

    $summary = (new SummarizeItineraryAction)->run(
        itineraryResponse: $responses->itinerary,
        checkoutResponse: $responses->checkout,
        addedRentalCarResponse: $responses->addedRentalCars,
        addedUpsellItemsResponse: collect($responses->addedUpsellItems),
    );

    $sumMock = m::mock(SummarizeItineraryAction::class);
    $sumMock->shouldReceive('run')->andReturn($summary);

    $component = new TripDetailsPage;
    $component->checkoutId = 'co-td-2';
    $component->itineraryId = 'it-td-2';
    $component->origin = 'ibe';
    $component->lang = 'de';

    $component->mount($callMock, $sumMock, $initMock);

    $view = $component->render();

    expect(method_exists($view, 'name'))->toBeTrue();
    expect($view->name())->toBe('checkout::blades.index');
});

it('priceChanged() updates itinerary price and promo response', function (): void {
    $responses = (new CallTripDetailsAction)->run('it-td-3', 'co-td-3');
    $model = Checkout::create(['checkout_id' => 'co-td-3', 'itinerary_id' => 'it-td-3', 'data' => []]);

    $callMock = m::mock(CallTripDetailsAction::class);
    $callMock->shouldReceive('run')->andReturn($responses);

    $initMock = m::mock(InitializeCheckoutDataAction::class);
    $initMock->shouldReceive('run')->andReturn($model);

    $summary = (new SummarizeItineraryAction)->run(
        itineraryResponse: $responses->itinerary,
        checkoutResponse: $responses->checkout,
        addedRentalCarResponse: $responses->addedRentalCars,
        addedUpsellItemsResponse: collect($responses->addedUpsellItems),
    );

    $sumMock = m::mock(SummarizeItineraryAction::class);
    $sumMock->shouldReceive('run')->andReturn($summary);

    $component = new TripDetailsPage;
    $component->checkoutId = 'co-td-3';
    $component->itineraryId = 'it-td-3';
    $component->origin = 'app';
    $component->lang = 'en';

    $component->mount($callMock, $sumMock, $initMock);

    $component->priceChanged([
        'discountedPackagePrice' => ['amount' => 999.99, 'currency' => 'CHF'],
        'totalPackagePrice' => ['amount' => 999.99, 'currency' => 'CHF'],
        'downPayment' => ['amount' => 999.99, 'currency' => 'CHF'],
        'packagePrice' => ['amount' => 1200.00, 'currency' => 'CHF'],
        'promoCode' => null,
        'externallyPaidCharges' => [
            'externallyPaidCharges' => [],
            'totalPrice' => ['amount' => 0, 'currency' => 'CHF'],
        ],
    ]);

    expect($component->itinerary->price->amount)->toBe(999.99)
        ->and($component->itinerary->price->currency)->toBe('CHF')
        ->and($component->itinerary->promoCodeResponse)->not->toBeNull();
});

it('createPaymentPageUrl() sets gateway, marks checkingAvailability and emits event', function (): void {
    $responses = (new CallTripDetailsAction)->run('it-td-4', 'co-td-4');
    $model = Checkout::create(['checkout_id' => 'co-td-4', 'itinerary_id' => 'it-td-4', 'data' => []]);

    $component = new TripDetailsPage;
    $component->checkoutId = 'co-td-4';
    $component->itineraryId = 'it-td-4';
    $component->origin = 'app';
    $component->lang = 'en';

    $callMock = m::mock(CallTripDetailsAction::class);
    $callMock->shouldReceive('run')->andReturn($responses);
    $initMock = m::mock(InitializeCheckoutDataAction::class);
    $initMock->shouldReceive('run')->andReturn($model);
    $sumMock = m::mock(SummarizeItineraryAction::class);
    $sumMock->shouldReceive('run')->andReturn((new SummarizeItineraryAction)->run(
        $responses->itinerary,
        $responses->checkout,
        $responses->addedRentalCars,
        collect($responses->addedUpsellItems)
    ));

    $component->mount($callMock, $sumMock, $initMock);

    $component->createPaymentPageUrl('encrypted-gateway');

    expect($component->gateway)->toBe('encrypted-gateway')
        ->and($component->checkingAvailability)->toBeTrue();
});

it('generatePaymentPageUrl() builds signed URL on success and resets checkingAvailability', function (): void {
    $responses = (new CallTripDetailsAction)->run('it-td-5', 'co-td-5');
    $model = Checkout::create(['checkout_id' => 'co-td-5', 'itinerary_id' => 'it-td-5', 'data' => []]);

    $component = new TripDetailsPage;
    $component->checkoutId = 'co-td-5';
    $component->itineraryId = 'it-td-5';
    $component->origin = 'ibe';
    $component->lang = 'de';

    $callMock = m::mock(CallTripDetailsAction::class);
    $callMock->shouldReceive('run')->andReturn($responses);
    $initMock = m::mock(InitializeCheckoutDataAction::class);
    $initMock->shouldReceive('run')->andReturn($model);
    $sumMock = m::mock(SummarizeItineraryAction::class);
    $sumMock->shouldReceive('run')->andReturn((new SummarizeItineraryAction)->run(
        $responses->itinerary,
        $responses->checkout,
        $responses->addedRentalCars,
        collect($responses->addedUpsellItems)
    ));

    $component->mount($callMock, $sumMock, $initMock);

    $component->gateway = 'enc-gw';
    $component->checkingAvailability = true;

    $component->generatePaymentPageUrl(true);

    expect($component->checkingAvailability)->toBeFalse();
    expect($component->paymentPageUrl)->not->toBeNull();

    $parts = parse_url((string) $component->paymentPageUrl);
    parse_str($parts['query'] ?? '', $query);

    expect($query['checkoutId'] ?? null)->toBe('co-td-5')
        ->and($query['itineraryId'] ?? null)->toBe('it-td-5')
        ->and($query['origin'] ?? null)->toBe('ibe')
        ->and($query['lang'] ?? null)->toBe('de')
        ->and($query['payment_method'] ?? null)->toBe('enc-gw')
        ->and(isset($query['signature']))->toBeTrue();
});

it('generatePaymentPageUrl(false) leaves URL null and sets checkingAvailability=false', function (): void {
    $responses = (new CallTripDetailsAction)->run('it-td-6', 'co-td-6');
    $model = Checkout::create(['checkout_id' => 'co-td-6', 'itinerary_id' => 'it-td-6', 'data' => []]);

    $component = new TripDetailsPage;
    $component->checkoutId = 'co-td-6';
    $component->itineraryId = 'it-td-6';
    $component->origin = 'app';
    $component->lang = 'en';

    $callMock = m::mock(CallTripDetailsAction::class);
    $callMock->shouldReceive('run')->andReturn($responses);
    $initMock = m::mock(InitializeCheckoutDataAction::class);
    $initMock->shouldReceive('run')->andReturn($model);
    $sumMock = m::mock(SummarizeItineraryAction::class);
    $sumMock->shouldReceive('run')->andReturn((new SummarizeItineraryAction)->run(
        $responses->itinerary,
        $responses->checkout,
        $responses->addedRentalCars,
        collect($responses->addedUpsellItems)
    ));

    $component->mount($callMock, $sumMock, $initMock);

    $component->gateway = 'x';
    $component->checkingAvailability = true;
    $component->paymentPageUrl = null;

    $component->generatePaymentPageUrl(false);

    expect($component->checkingAvailability)->toBeFalse()
        ->and($component->paymentPageUrl)->toBeNull();
});
