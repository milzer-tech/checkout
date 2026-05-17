<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Checkout\VerifyAvailabilityAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargeResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TermsAndConditionsResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TextSectionResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Livewire\PaymentOptionsSection;
use Nezasa\Checkout\Livewire\Stepper;
use Nezasa\Checkout\Livewire\TermsSection;
use Nezasa\Checkout\Livewire\TripSummary;
use Nezasa\Checkout\Models\Checkout;

final class ExposedTermsSectionForWorkflowTest extends TermsSection
{
    /**
     * @return array<string, array<string, string>>
     */
    public function exposedRules(): array
    {
        return $this->rules();
    }
}

final class PaymentProviderActionForWorkflowTest extends GetPaymentProviderAction
{
    public function run(): array
    {
        return [
            new PaymentOption('Invoice', encrypt('Invoice'), encrypt('InvoiceGateway')),
        ];
    }
}

function livewireWorkflowCheckout(array $data = [], bool $restPayment = false): Checkout
{
    return Checkout::factory()->create([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'APP',
        'lang' => 'en',
        'rest_payment' => $restPayment,
        'data' => array_replace_recursive([
            'status' => Checkout::buildSectionStatus(),
            'acceptedTerms' => [],
        ], $data),
    ]);
}

function livewireWorkflowPrice(float $total = 1000.0, float $downPayment = 250.0): PriceResponse
{
    return new PriceResponse(
        discountedPackagePrice: new Price($total, 'EUR'),
        packagePrice: new Price($total, 'EUR'),
        totalPackagePrice: new Price($total, 'EUR'),
        downPayment: new Price($downPayment, 'EUR'),
        openAmount: new Price($total - $downPayment, 'EUR'),
        externallyPaidCharges: new ExternallyPaidChargesResponseEntity(new Price(0.0, 'EUR')),
        showTotalPrice: new Price($total, 'EUR'),
        showPaymentPrice: new Price($downPayment, 'EUR'),
    );
}

function livewireWorkflowItinerary(?PriceResponse $price = null): ItinerarySummary
{
    return new ItinerarySummary(
        price: $price ?? livewireWorkflowPrice(),
        title: 'Workflow trip',
        startDate: CarbonImmutable::parse('2025-09-01'),
        endDate: CarbonImmutable::parse('2025-09-10'),
        adults: 1,
        destinationCountries: new Collection(['DE']),
    );
}

function primeBaseCheckoutComponent(TripSummary|PaymentOptionsSection|TermsSection $component, Checkout $checkout): void
{
    $component->model = $checkout;
    $component->checkoutId = $checkout->checkout_id;
    $component->itineraryId = $checkout->itinerary_id;
    $component->origin = $checkout->origin;
    $component->lang = $checkout->lang;
    $component->restPayment = $checkout->rest_payment;
}

it('maps stepper state from route names', function (): void {
    $stepper = new Stepper;

    $stepper->currentPath = 'traveler-details';
    expect($stepper->getCurrentStepIndex())->toBe(1)
        ->and($stepper->isActive('traveler-details'))->toBeTrue()
        ->and($stepper->isCompleted('1'))->toBeTrue()
        ->and($stepper->isCompleted('2'))->toBeFalse();

    $stepper->currentPath = 'payment';
    expect($stepper->getCurrentStepIndex())->toBe(2)
        ->and($stepper->isCompleted('2'))->toBeTrue();

    $stepper->currentPath = 'payment-result';
    expect($stepper->getCurrentStepIndex())->toBe(3)
        ->and($stepper->isCompleted('3'))->toBeTrue();
});

it('loads available payment options and expands rest-payment checkout options immediately', function (): void {
    $checkout = livewireWorkflowCheckout(restPayment: true);
    $component = new PaymentOptionsSection;
    primeBaseCheckoutComponent($component, $checkout);

    $component->mount(new PaymentProviderActionForWorkflowTest);

    expect($component->options)->toHaveCount(1)
        ->and($component->options[0]->decryptGateway())->toBe('Invoice')
        ->and($component->isExpanded)->toBeTrue();

    $component->resetSection([Section::PaymentOptions->value]);

    expect($component->isCompleted)->toBeFalse()
        ->and($component->isExpanded)->toBeFalse();
});

it('builds terms validation rules from itinerary and selected insurance terms', function (): void {
    $section = new TextSectionResponseEntity(
        header: 'Supplier terms',
        text: 'Please accept supplier terms',
        checkboxText: 'I accept supplier terms',
        supplierId: 'supplier-1'
    );
    $checkout = livewireWorkflowCheckout([
        'acceptedTerms' => [
            $section->getKey() => true,
            'unchecked' => false,
        ],
        'insurance' => [
            'offer' => [
                'id' => 'insurance-1',
                'title' => 'Insurance',
                'price' => ['amount' => 15.0, 'currency' => 'EUR'],
                'coverage' => [],
                'terms' => [
                    'checkboxText' => 'I accept insurance terms',
                    'conditions' => ['<a href="https://example.test">Terms</a>'],
                ],
            ],
        ],
    ]);

    $component = new ExposedTermsSectionForWorkflowTest;
    primeBaseCheckoutComponent($component, $checkout);
    $component->termsAndConditions = new TermsAndConditionsResponseEntity(new Collection([$section]));
    $component->mount();
    $component->listen();

    expect($component->acceptedTerms)->toBe([$section->getKey() => true])
        ->and($component->insuranceTerms?->checkboxText)->toBe('I accept insurance terms')
        ->and($component->exposedRules())->toHaveKeys([
            'acceptedTerms.'.$section->getKey(),
            'acceptedInsurance.'.$component->insuranceTerms->getKey(),
        ]);

    $component->openTermsModal(0);
    expect($component->showTermsModal)->toBeTrue()
        ->and($component->modalTermIndex)->toBe(0);

    $component->closeTermsModal();
    expect($component->showTermsModal)->toBeFalse()
        ->and($component->modalTermIndex)->toBeNull();
});

it('keeps trip summary pricing and insurance in sync when insurance is selected or declined', function (): void {
    $calls = (object) ['count' => 0];
    app()->bind(VerifyAvailabilityAction::class, fn (): VerifyAvailabilityAction => new class($calls) extends VerifyAvailabilityAction
    {
        public function __construct(private readonly object $calls) {}

        public function run(CheckoutParamsDto $params, ItinerarySummary $itinerary): bool
        {
            $this->calls->count++;

            return true;
        }
    });

    $checkout = livewireWorkflowCheckout();
    $component = new TripSummary;
    primeBaseCheckoutComponent($component, $checkout);
    $component->itinerary = livewireWorkflowItinerary(livewireWorkflowPrice(total: 1000.0, downPayment: 250.0));

    $component->addInsurance(
        ['id' => 'insurance-1', 'name' => 'Insurance', 'availability' => null],
        ['amount' => 15.5, 'currency' => 'EUR']
    );

    expect($component->itinerary->insurances)->toHaveCount(1)
        ->and($component->itinerary->price->showTotalPrice->amount)->toBe(1015.5)
        ->and($component->itinerary->price->showPaymentPrice->amount)->toBe(265.5)
        ->and($calls->count)->toBe(1);

    $component->removeInsurance();

    expect($component->itinerary->insurances)->toBeEmpty()
        ->and($component->itinerary->price->showTotalPrice->amount)->toBe(1000.0)
        ->and($component->itinerary->price->showPaymentPrice->amount)->toBe(250.0)
        ->and($calls->count)->toBe(2);
});

it('keeps externally paid insurance selected without adding it to trip summary pricing', function (): void {
    $calls = (object) ['count' => 0];
    app()->bind(VerifyAvailabilityAction::class, fn (): VerifyAvailabilityAction => new class($calls) extends VerifyAvailabilityAction
    {
        public function __construct(private readonly object $calls) {}

        public function run(CheckoutParamsDto $params, ItinerarySummary $itinerary): bool
        {
            $this->calls->count++;

            return true;
        }
    });

    $checkout = livewireWorkflowCheckout();
    $component = new TripSummary;
    primeBaseCheckoutComponent($component, $checkout);
    $component->itinerary = livewireWorkflowItinerary(livewireWorkflowPrice(total: 1000.0, downPayment: 250.0));

    $component->addInsurance(
        ['id' => 'ergo-insurance-1', 'name' => 'ERGO Insurance', 'availability' => null],
        ['amount' => 15.5, 'currency' => 'EUR'],
        false
    );

    expect($component->itinerary->insurances)->toHaveCount(1)
        ->and($component->itinerary->price->showTotalPrice->amount)->toBe(1000.0)
        ->and($component->itinerary->price->showPaymentPrice->amount)->toBe(250.0)
        ->and($calls->count)->toBe(1);
});

it('shows trip price breakdown for external charges, down payments, and rest payments', function (): void {
    $price = livewireWorkflowPrice(total: 1000.0, downPayment: 250.0);
    $price->externallyPaidCharges->externallyPaidCharges = new Collection([
        new ExternallyPaidChargeResponseEntity(
            name: 'Destination fee',
            productName: 'City tax',
            value: new Price(10.0, 'EUR')
        ),
    ]);
    $checkout = livewireWorkflowCheckout(restPayment: true);
    $component = new TripSummary;
    primeBaseCheckoutComponent($component, $checkout);
    $component->itinerary = livewireWorkflowItinerary($price);

    $component->mount();

    expect($component->hasDestinationCost)->toBeTrue()
        ->and($component->showPriceBreakdown)->toBeTrue();

    $component->togglePriceBreakdown();
    expect($component->showPriceBreakdown)->toBeFalse();
});
