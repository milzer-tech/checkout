<?php

use Illuminate\Http\Request;
use Mockery as m;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Handlers\PaymentCallBackHandler;

beforeEach(function (): void {
    fakeInitialNezasaCalls();
});

afterEach(function (): void {
    m::close();
});

it('mount processes callback output, builds travelers, and sets itinerary price from latest transaction', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-res-1',
        'itinerary_id' => 'it-res-1',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [
            'paxInfo' => [
                [
                    ['firstName' => 'Anna', 'lastName' => 'Jones'],
                    ['firstName' => 'Ben', 'lastName' => 'Miller'],
                ],
                [
                    ['firstName' => 'Cara', 'lastName' => 'Stone'],
                ],
            ],
        ],
    ]);

    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'oppwa',
        'amount' => '987.65',
        'currency' => 'CHF',
    ]);

    $handler = m::mock(PaymentCallBackHandler::class);
    $handler->shouldReceive('run')
        ->once()
        ->withArgs(fn ($transaction, $request): bool => $transaction instanceof Transaction && $request instanceof Request)
        ->andReturn(new PaymentOutput(
            gatewayName: 'oppwa',
            isNezasaBookingSuccessful: true,
            bookingReference: 'BR-123',
            orderDate: null,
            data: ['foo' => 'bar']
        ));

    app()->instance(PaymentCallBackHandler::class, $handler);

    $component = new PaymentResultPage;
    $component->checkoutId = 'co-res-1';
    $component->itineraryId = 'it-res-1';
    $component->origin = 'app';
    $component->lang = 'en';
    // The component expects transaction to be set before mount()
    $component->transaction = $tx;

    $request = Request::create('/payment/result', 'GET', ['x' => 'y']);

    $component->mount($request);

    expect($component->output)
        ->toBeInstanceOf(PaymentOutput::class)
        ->and($component->output->bookingReference)->toBe('BR-123');

    expect($component->travelers)
        ->toBe([
            'Anna Jones',
            'Ben Miller',
            'Cara Stone',
        ]);

    expect($component->itinerary->price->downPayment->amount)->toBe(51.0)
        ->and($component->itinerary->price->downPayment->currency)->toBe('EUR');

    expect($component->model->lastestTransaction->id)->toBe($tx->id);
});

it('render returns the confirmation page view', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-res-2',
        'itinerary_id' => 'it-res-2',
        'origin' => 'app',
        'lang' => 'en',
        'data' => [
            'paxInfo' => [
                [
                    ['firstName' => 'Only', 'lastName' => 'One'],
                ],
            ],
        ],
    ]);

    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'oppwa',
        'amount' => '10.00',
        'currency' => 'USD',
    ]);

    $handler = m::mock(PaymentCallBackHandler::class);
    $handler->shouldReceive('run')->andReturn(new PaymentOutput(
        gatewayName: 'oppwa',
        isNezasaBookingSuccessful: true
    ));
    app()->instance(PaymentCallBackHandler::class, $handler);

    $component = new PaymentResultPage;
    $component->checkoutId = 'co-res-2';
    $component->itineraryId = 'it-res-2';
    $component->origin = 'app';
    $component->lang = 'en';
    // The component expects transaction to be set before mount()
    $component->transaction = $tx;

    $component->mount(Request::create('/payment/result', 'GET'));

    $view = $component->render();

    expect(method_exists($view, 'name'))->toBeTrue();
    expect($view->name())->toBe('checkout::blades.confirmation-page');
});
