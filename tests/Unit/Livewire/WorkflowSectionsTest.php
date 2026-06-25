<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Checkout\VerifyAvailabilityAction;
use Nezasa\Checkout\Actions\TravelInformation\LoadTravelInformationAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\EuPrrlLinkResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\EuPrrlResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargeResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\OnRequestResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TermsAndConditionsResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TextSectionResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TravelInformationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RegulatoryInformationResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Passolution\Requests\GetContentRequest;
use Nezasa\Checkout\Livewire\PaymentOptionsSection;
use Nezasa\Checkout\Livewire\Stepper;
use Nezasa\Checkout\Livewire\TermsSection;
use Nezasa\Checkout\Livewire\TravelInformationSection;
use Nezasa\Checkout\Livewire\TripSummary;
use Nezasa\Checkout\Models\Checkout;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

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

function primeBaseCheckoutComponent(TripSummary|PaymentOptionsSection|TermsSection|TravelInformationSection $component, Checkout $checkout): void
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

it('persists on-request confirmation and owns its error state', function (): void {
    $onRequest = new OnRequestResponseEntity;
    $checkout = livewireWorkflowCheckout();
    $component = new PaymentOptionsSection;
    primeBaseCheckoutComponent($component, $checkout);
    $component->regulatoryInformation = new RegulatoryInformationResponse(onRequest: $onRequest);
    $component->isOnRequest = true;
    $component->mount(new PaymentProviderActionForWorkflowTest);

    $component->showOnRequestConfirmationError();

    expect($component->showOnRequestTermsError)->toBeFalse();

    $component->toggleOnRequestTerms(true);
    $checkout->refresh();

    expect(data_get($checkout->data, 'acceptedTerms.'.$onRequest->getConfirmationKey()))->toBeTrue()
        ->and($component->acceptedOnRequestTerms)->toBeTrue()
        ->and($component->showOnRequestTermsError)->toBeFalse();
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
                    'conditions' => [],
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

it('requires EU-PRRL general terms confirmation when enabled', function (): void {
    $checkout = livewireWorkflowCheckout();
    $component = new ExposedTermsSectionForWorkflowTest;
    primeBaseCheckoutComponent($component, $checkout);
    $component->termsAndConditions = new TermsAndConditionsResponseEntity;
    $component->euPrrl = new EuPrrlResponseEntity(
        generalTermsConfirmationEnabled: true,
        itineraryContentValidationEnabled: true,
        title: 'EU package travel',
        intro: '<p>Please confirm the package travel terms.</p>',
        checkboxText: 'I accept the EU package travel terms',
        links: new Collection([
            new EuPrrlLinkResponseEntity(
                url: 'https://example.com/eu-prrl',
                linkText: 'EU-PRRL information'
            ),
        ])
    );

    $component->mount();

    expect($component->requiresEuPrrlGeneralTermsConfirmation())->toBeTrue()
        ->and($component->exposedRules())->toHaveKey('acceptedEuPrrlTerms');

    expect(fn () => $component->next())->toThrow(ValidationException::class);

    $component->toggleEuPrrlTerms(true);
    $component->next();
    $checkout->refresh();

    expect($component->isCompleted)->toBeTrue()
        ->and($component->isExpanded)->toBeFalse()
        ->and(data_get($checkout->data, 'acceptedTerms.'.$component->euPrrl->getGeneralTermsKey()))->toBeTrue();
});

it('does not require EU-PRRL general terms confirmation when disabled', function (): void {
    $checkout = livewireWorkflowCheckout();
    $component = new ExposedTermsSectionForWorkflowTest;
    primeBaseCheckoutComponent($component, $checkout);
    $component->termsAndConditions = new TermsAndConditionsResponseEntity;
    $component->euPrrl = new EuPrrlResponseEntity(
        generalTermsConfirmationEnabled: false,
        itineraryContentValidationEnabled: true,
    );

    $component->mount();

    expect($component->requiresEuPrrlGeneralTermsConfirmation())->toBeFalse()
        ->and($component->exposedRules())->not->toHaveKey('acceptedEuPrrlTerms');
});

it('loads Passolution travel information for every destination and nationality combination', function (): void {
    config()->set('checkout.integrations.passolution.active', true);
    config()->set('checkout.integrations.passolution.token', 'test-token');

    $mockClient = MockClient::global([
        GetContentRequest::class => MockResponse::make([
            'records' => [
                [
                    'destination' => 'DE',
                    'nationality' => 'IR',
                    'title' => 'Destination Germany / Nationality Iran',
                    'entry' => ['content' => 'A passport is required.'],
                    'visa' => ['content' => 'No visa is required.'],
                    'transit_visa' => ['content' => 'Transit visa is not required.'],
                    'health' => ['content' => 'No vaccinations are required.'],
                ],
            ],
        ]),
    ]);

    $checkout = livewireWorkflowCheckout([
        'paxInfo' => [
            [
                ['nationality' => 'IR-IRAN'],
            ],
        ],
        'status' => array_replace_recursive(Checkout::buildSectionStatus(), [
            Section::TermsAndConditions->value => ['isCompleted' => true],
        ]),
    ]);
    $component = new TravelInformationSection;
    primeBaseCheckoutComponent($component, $checkout);
    $component->itinerary = livewireWorkflowItinerary();
    $component->regulatoryInformation = new RegulatoryInformationResponse(
        travelInformation: new TravelInformationResponseEntity(confirmationEnabled: true)
    );

    $component->mount(new LoadTravelInformationAction);

    expect($component->shouldRender())->toBeTrue()
        ->and($component->combinations)->toHaveCount(1)
        ->and($component->combinations[0]['title'])->toBe('Destination Germany / Nationality Iran')
        ->and($component->combinations[0]['health'])->toBe('No vaccinations are required.')
        ->and($component->combinations[0]['entry'])->toBe('A passport is required.')
        ->and($component->combinations[0]['visa'])->toBe('No visa is required.')
        ->and($component->combinations[0]['transit_visa'])->toBe('Transit visa is not required.');

    $mockClient->assertSent(function (mixed $request): bool {
        if (! $request instanceof GetContentRequest) {
            return false;
        }

        expect($request->resolveEndpoint())->toBe('/content/all/text')
            ->and($request->query()->all())->toBe([
                'lang' => 'en',
                'countries' => 'de',
                'nat' => 'ir',
            ]);

        return true;
    });
});

it('requires and persists travel information confirmation before continuing', function (): void {
    config()->set('checkout.integrations.passolution.active', true);
    config()->set('checkout.integrations.passolution.token', 'test-token');

    $checkout = livewireWorkflowCheckout([
        'paxInfo' => [
            [
                ['nationalityCountryCode' => 'EG'],
            ],
        ],
    ]);
    $component = new TravelInformationSection;
    primeBaseCheckoutComponent($component, $checkout);
    $component->itinerary = livewireWorkflowItinerary();
    $component->regulatoryInformation = new RegulatoryInformationResponse(
        travelInformation: new TravelInformationResponseEntity(confirmationEnabled: true)
    );

    expect(fn () => $component->next())->toThrow(ValidationException::class);

    $component->toggleTravelInformationConfirmation(true);
    $component->next();
    $checkout->refresh();

    expect(data_get($checkout->data, 'travel_information_confirmed'))->toBeTrue()
        ->and(data_get($checkout->data, 'travel_information_confirmation_hash'))->not->toBeNull()
        ->and($component->isCompleted)->toBeTrue()
        ->and($component->isExpanded)->toBeFalse();
});

it('resets travel information confirmation when destinations or nationalities change', function (): void {
    config()->set('checkout.integrations.passolution.active', true);
    config()->set('checkout.integrations.passolution.token', 'test-token');

    $checkout = livewireWorkflowCheckout([
        'paxInfo' => [
            [
                ['nationalityCountryCode' => 'EG'],
            ],
        ],
        'travel_information_confirmed' => true,
        'travel_information_confirmation_hash' => 'stale-hash',
        'status' => array_replace_recursive(Checkout::buildSectionStatus(), [
            Section::TravelInformation->value => ['isCompleted' => true],
        ]),
    ]);
    $component = new TravelInformationSection;
    primeBaseCheckoutComponent($component, $checkout);
    $component->itinerary = livewireWorkflowItinerary();
    $component->regulatoryInformation = new RegulatoryInformationResponse(
        travelInformation: new TravelInformationResponseEntity(confirmationEnabled: true)
    );
    $component->isCompleted = true;

    $component->mount(new LoadTravelInformationAction);
    $checkout->refresh();

    expect($component->travelInformationConfirmed)->toBeFalse()
        ->and($component->isCompleted)->toBeFalse()
        ->and(data_get($checkout->data, 'travel_information_confirmed'))->toBeFalse()
        ->and(data_get($checkout->data, 'travel_information_confirmation_hash'))->toBeNull()
        ->and(data_get($checkout->data, 'status.'.Section::TravelInformation->value.'.isCompleted'))->toBeFalse();
});

it('resets stale travel information confirmation when traveller nationality changes before continuing again', function (): void {
    config()->set('checkout.integrations.passolution.active', true);
    config()->set('checkout.integrations.passolution.token', 'test-token');

    MockClient::global([
        GetContentRequest::class => MockResponse::make([
            'records' => [],
        ]),
    ]);

    $checkout = livewireWorkflowCheckout([
        'paxInfo' => [
            [
                ['nationalityCountryCode' => 'EG'],
            ],
        ],
    ]);
    $component = new TravelInformationSection;
    primeBaseCheckoutComponent($component, $checkout);
    $component->itinerary = livewireWorkflowItinerary();
    $component->regulatoryInformation = new RegulatoryInformationResponse(
        travelInformation: new TravelInformationResponseEntity(confirmationEnabled: true)
    );

    $component->toggleTravelInformationConfirmation(true);
    $component->next();
    $checkout->refresh();

    expect(data_get($checkout->data, 'travel_information_confirmed'))->toBeTrue();

    $checkout->updateData([
        'paxInfo.0.0.nationalityCountryCode' => 'FR',
        'status.'.Section::TravelInformation->value.'.isCompleted' => false,
    ]);
    $component->isCompleted = true;
    $component->travelInformationConfirmed = true;

    $component->listen(new LoadTravelInformationAction);
    $checkout->refresh();

    expect($component->travelInformationConfirmed)->toBeFalse()
        ->and($component->isCompleted)->toBeFalse()
        ->and(data_get($checkout->data, 'travel_information_confirmed'))->toBeFalse()
        ->and(data_get($checkout->data, 'travel_information_confirmation_hash'))->toBeNull();
});

it('loads EU-PRRL terms acceptance only for the current content hash', function (): void {
    $acceptedEuPrrl = new EuPrrlResponseEntity(
        generalTermsConfirmationEnabled: true,
        itineraryContentValidationEnabled: true,
        title: 'Accepted EU package travel',
        intro: '<p>Accepted terms.</p>',
        checkboxText: 'I accept accepted terms',
        links: new Collection([
            new EuPrrlLinkResponseEntity(
                url: 'https://example.com/accepted',
                linkText: 'Accepted link'
            ),
        ])
    );

    $changedEuPrrl = new EuPrrlResponseEntity(
        generalTermsConfirmationEnabled: true,
        itineraryContentValidationEnabled: true,
        title: 'Changed EU package travel',
        intro: '<p>Accepted terms.</p>',
        checkboxText: 'I accept accepted terms',
        links: new Collection([
            new EuPrrlLinkResponseEntity(
                url: 'https://example.com/accepted',
                linkText: 'Accepted link'
            ),
        ])
    );

    $checkout = livewireWorkflowCheckout([
        'acceptedTerms' => [
            $acceptedEuPrrl->getGeneralTermsKey() => true,
        ],
    ]);

    $component = new ExposedTermsSectionForWorkflowTest;
    primeBaseCheckoutComponent($component, $checkout);
    $component->termsAndConditions = new TermsAndConditionsResponseEntity;
    $component->euPrrl = $acceptedEuPrrl;
    $component->mount();

    expect($component->acceptedEuPrrlTerms)->toBeTrue();

    $component = new ExposedTermsSectionForWorkflowTest;
    primeBaseCheckoutComponent($component, $checkout);
    $component->termsAndConditions = new TermsAndConditionsResponseEntity;
    $component->euPrrl = $changedEuPrrl;
    $component->mount();

    expect($component->acceptedEuPrrlTerms)->toBeFalse();
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
        false,
        'The insurance for 15.50 EUR is paid separately by SEPA direct debit.'
    );

    expect($component->itinerary->insurances)->toHaveCount(1)
        ->and($component->itinerary->price->showTotalPrice->amount)->toBe(1000.0)
        ->and($component->itinerary->price->showPaymentPrice->amount)->toBe(250.0)
        ->and($component->separateInsurancePaymentNotice)->toBe('The insurance for 15.50 EUR is paid separately by SEPA direct debit.')
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
