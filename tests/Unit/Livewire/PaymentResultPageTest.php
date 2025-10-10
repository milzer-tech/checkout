<?php

use Illuminate\Http\Request;
use Mockery as m;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Handlers\WidgetCallBackHandler;

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
        'gateway' => PaymentGatewayEnum::Oppwa,
        'amount' => '987.65',
        'currency' => 'CHF',
    ]);

    $handler = m::mock(WidgetCallBackHandler::class);
    $handler->shouldReceive('run')
        ->once()
        ->withArgs(fn ($transaction, $request): bool => $transaction instanceof Transaction && $request instanceof Request)
        ->andReturn(new PaymentOutput(
            gatewayName: PaymentGatewayEnum::Oppwa,
            isNezasaBookingSuccessful: true,
            bookingReference: 'BR-123',
            orderDate: null,
            data: ['foo' => 'bar']
        ));

    app()->instance(WidgetCallBackHandler::class, $handler);

    $component = new PaymentResultPage;
    $component->checkoutId = 'co-res-1';
    $component->itineraryId = 'it-res-1';
    $component->origin = 'app';
    $component->lang = 'en';

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

    expect($component->itinerary->price->amount)->toBe(987.65)
        ->and($component->itinerary->price->currency)->toBe('CHF');

    expect($component->model->lastestTransaction->id)->toBe($tx->id);
});

it('render returns the confirmation page view', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-res-2',
        'itinerary_id' => 'it-res-2',
        'data' => [
            'paxInfo' => [
                [
                    ['firstName' => 'Only', 'lastName' => 'One'],
                ],
            ],
        ],
    ]);

    Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => PaymentGatewayEnum::Oppwa,
        'amount' => '10.00',
        'currency' => 'USD',
    ]);

    $handler = m::mock(WidgetCallBackHandler::class);
    $handler->shouldReceive('run')->andReturn(new PaymentOutput(
        gatewayName: PaymentGatewayEnum::Oppwa,
        isNezasaBookingSuccessful: true
    ));
    app()->instance(WidgetCallBackHandler::class, $handler);

    $component = new PaymentResultPage;
    $component->checkoutId = 'co-res-2';
    $component->itineraryId = 'it-res-2';
    $component->origin = 'app';
    $component->lang = 'en';

    $component->mount(Request::create('/payment/result', 'GET'));

    $view = $component->render();

    expect(method_exists($view, 'name'))->toBeTrue();
    expect($view->name())->toBe('checkout::blades.confirmation-page');
});
