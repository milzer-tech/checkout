<?php

use Illuminate\Http\RedirectResponse;
use Mockery as m;
use Nezasa\Checkout\Actions\Checkout\FindCheckoutModelAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaWidgetGateway;
use Nezasa\Checkout\Payments\Handlers\PaymentInitiationHandler;

afterEach(function (): void {
    m::close();
});

it('mount() initializes itinerary via trip details and sets payment via widget handler', function (): void {
    // Ensure Oppwa gateway is active for provider discovery
    config()->set('checkout.integrations.oppwa.active', true);

    // Arrange request query parameters
    request()->query->set('payment_method', encrypt('oppwa'));
    request()->merge(['lang' => 'en']);

    // Seed a checkout model that FindCheckoutModelAction should return
    $model = Checkout::create([
        'checkout_id' => 'co-pay-1',
        'itinerary_id' => 'it-1',
        'data' => [
            'contact' => [
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'email' => 'jane@example.com',
            ],
        ],
    ]);

    // Bind FindCheckoutModelAction to return our model
    $finder = m::mock(FindCheckoutModelAction::class);
    $finder->shouldReceive('run')
        ->once()
        ->with('co-pay-1', 'it-1')
        ->andReturn($model);
    app()->instance(FindCheckoutModelAction::class, $finder);

    // Fake external Nezasa calls used inside CallTripDetailsAction / SummarizeItineraryAction
    fakeInitialNezasaCalls();

    // Bind WidgetInitiationHandler to validate input and return a PaymentAsset
    $widget = m::mock(PaymentInitiationHandler::class);
    $widget->shouldReceive('run')
        ->once()
        ->withArgs(function ($passedModel, $price, $gateway): bool {
            // Validate passed model
            expect($passedModel)->toBeInstanceOf(Checkout::class)
                ->and($passedModel->checkout_id)->toBe('co-pay-1');

            // Validate gateway instance
            expect($gateway)->toBeInstanceOf(OppwaWidgetGateway::class);

            // Validate price object (down payment) — amount comes from fixtures
            expect($price)->toBeInstanceOf(Price::class)
                ->and($price->amount)->toBeFloat();

            return true;
        })
        ->andReturn(new PaymentAsset(true, html: '<div>widget</div>'));
    app()->instance(PaymentInitiationHandler::class, $widget);

    // Instantiate the component and set URL-bound properties
    $component = new PaymentPage;
    $component->checkoutId = 'co-pay-1';
    $component->itineraryId = 'it-1';
    $component->origin = 'app';
    $component->lang = 'en';

    // Act
    $component->mount();

    // Assert
    expect($component->itinerary)->not->toBeNull()
        ->and($component->payment)->toBeInstanceOf(PaymentAsset::class)
        ->and($component->model->is($model))->toBeTrue();
});

it('render() returns the payment page view and goBack() redirects to traveler-details with params', function (): void {
    // Ensure Oppwa gateway is active for provider discovery
    config()->set('checkout.integrations.oppwa.active', true);

    // Arrange basic state
    request()->query->set('payment_method', encrypt('oppwa'));
    request()->merge(['lang' => 'en']);

    $model = Checkout::create([
        'checkout_id' => 'co-pay-2',
        'itinerary_id' => 'it-2',
        'data' => [
            'contact' => [
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john@example.com',
            ],
        ],
    ]);

    $finder = m::mock(FindCheckoutModelAction::class);
    $finder->shouldReceive('run')->andReturn($model);
    app()->instance(FindCheckoutModelAction::class, $finder);

    fakeInitialNezasaCalls();

    $widget = m::mock(PaymentInitiationHandler::class);
    $widget->shouldReceive('run')->andReturn(new PaymentAsset(true));
    app()->instance(PaymentInitiationHandler::class, $widget);

    $component = new PaymentPage;
    $component->checkoutId = 'co-pay-2';
    $component->itineraryId = 'it-2';
    $component->origin = 'ibe';
    $component->lang = 'de';

    $component->mount();

    // render view
    $view = $component->render();
    expect($view->name())->toBe('checkout::blades.payment-page');

    // goBack
    /** @var RedirectResponse $redirect */
    $redirect = $component->goBack();

    $expectedUrl = route('traveler-details', [
        'checkoutId' => 'co-pay-2',
        'itineraryId' => 'it-2',
        'origin' => 'ibe',
        'lang' => 'de',
    ]);

    expect($redirect->getTargetUrl())->toBe($expectedUrl);
});
