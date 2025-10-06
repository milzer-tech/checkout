<?php

use Nezasa\Checkout\Livewire\ConfirmationPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;

beforeEach(function (): void {
    fakeInitialNezasaCalls();
});

it('mount builds travelers list from paxInfo and sets itinerary price from latest transaction', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-conf-1',
        'itinerary_id' => 'it-conf-1',
        'data' => [
            'paxInfo' => [
                [
                    ['firstName' => 'Alice', 'lastName' => 'Doe'],
                    ['firstName' => 'Bob', 'lastName' => 'Smith'],
                ],
                [
                    ['firstName' => 'Charlie', 'lastName' => 'Brown'],
                ],
            ],
        ],
    ]);

    $tx = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => PaymentGatewayEnum::Oppwa,
        'amount' => '1234.56',
        'currency' => 'EUR',
    ]);

    $component = new ConfirmationPage;
    $component->checkoutId = 'co-conf-1';
    $component->itineraryId = 'it-conf-1';
    $component->origin = 'app';
    $component->lang = 'en';

    $component->mount();

    expect($component->travelers)
        ->toBe([
            'Alice Doe',
            'Bob Smith',
            'Charlie Brown',
        ]);

    expect($component->itinerary->price->amount)->toBe(1234.56)
        ->and($component->itinerary->price->currency)->toBe('EUR');

    expect($component->model->lastestTransaction->id)->toBe($tx->id);
});

it('render returns the confirmation page view', function (): void {
    $checkout = Checkout::create([
        'checkout_id' => 'co-conf-2',
        'itinerary_id' => 'it-conf-2',
        'data' => ['paxInfo' => [[['firstName' => 'A', 'lastName' => 'B']]]],
    ]);

    Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => PaymentGatewayEnum::Oppwa,
        'amount' => '10.00',
        'currency' => 'USD',
    ]);

    $component = new ConfirmationPage;
    $component->checkoutId = 'co-conf-2';
    $component->itineraryId = 'it-conf-2';
    $component->origin = 'app';
    $component->lang = 'en';

    $component->mount();

    $view = $component->render();

    expect(method_exists($view, 'name'))->toBeTrue();
    expect($view->name())->toBe('checkout::blades.confirmation-page');
});
