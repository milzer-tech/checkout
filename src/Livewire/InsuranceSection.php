<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Intervention\Validation\Rules\Iban;
use Livewire\Attributes\On;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Facades\AvailabilityFacade;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsurancePaymentFieldDto;
use Nezasa\Checkout\Insurances\Handlers\InsuranceHandler;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Jobs\VerifyAvailabilityJob;

class InsuranceSection extends BaseCheckoutComponent
{
    public ItinerarySummary $itinerary;

    public ?ContactInfoPayloadEntity $contact = null;

    public bool $insuranceSelected = false;

    public ?string $selectedOfferId = null;

    public bool $isLoadingOffers = false;

    /**
     * @var array<int, InsuranceOfferDto>
     */
    public array $offers = [];

    public bool $isInsuranceAvailable = false;

    public ?bool $insuranceProviderIsAvailable = null;

    public string $notAvailableMessage;

    public bool $shouldInitVerticalWidget = false;

    /**
     * Payment data requested by the active insurance provider.
     *
     * @var array<string, string|null>
     */
    public array $insurancePaymentData = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $insurancePaymentFields = [];

    public bool $requiresInsurancePaymentData = false;

    public ?string $insuranceProviderName = null;

    public ?string $insuranceProviderLogo = null;

    /**
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(InsuranceHandler $insuranceHandler): void
    {
        $this->notAvailableMessage = trans('checkout::page.trip_details.insurance_not_available');
        $this->isInsuranceAvailable = $insuranceHandler->isAvailable();
        $this->insuranceProviderName = $insuranceHandler->getProviderName();
        $this->insuranceProviderLogo = $insuranceHandler->getProviderLogo();

        if (! $this->isInsuranceAvailable) {
            $this->next();

            return;
        }

        if (isset($this->model->data['contact'])) {
            $this->contact = ContactInfoPayloadEntity::from($this->model->data['contact']);
        }

        // Do not restore payment details after a full page load (refresh or revisit).
        // They are re-captured in-session; clear any persisted value so inputs stay empty.
        $this->insurancePaymentData = [];
        $this->insurancePaymentFields = [];
        $this->requiresInsurancePaymentData = false;
        $checkoutArr = InsuranceCheckoutData::checkoutDataArray($this->model->data);
        $bucket = InsuranceCheckoutData::getNormalizedInsuranceBucket($checkoutArr);
        if ($bucket !== null) {
            $bucket[InsuranceCheckoutData::PAYMENT] = null;
            $this->model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate($bucket));
        }
    }

    public function updateSelectedOfferId(?string $id): void
    {
        $offer = collect($this->offers)->firstWhere('id', $id);

        if (is_null($offer)) {
            $this->selectedOfferId = null;
            $this->requiresInsurancePaymentData = false;
            $this->insurancePaymentFields = [];
            $this->insurancePaymentData = [];
            $this->model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate(null));
            $this->dispatch('insurance-declined');

            return;
        }

        $this->selectedOfferId = $id;
        $bucket = $this->insuranceBucketWithCreateOfferContext();
        $bucket[InsuranceCheckoutData::OFFER] = $offer->toArray();

        $this->loadInsurancePaymentFields();
        if (! $this->requiresInsurancePaymentData) {
            $this->insurancePaymentData = [];
            $bucket[InsuranceCheckoutData::PAYMENT] = null;
        } else {
            $this->insurancePaymentData = $this->paymentDataForFields($this->insurancePaymentData);
        }

        $this->model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate($bucket));

        $insuranceHandler = resolve(InsuranceHandler::class);

        $this->dispatch(
            'insurance-selected',
            new InsuranceItem($id, $offer->title),
            $offer->price,
            $insuranceHandler->shouldAddOfferPriceToPayment(),
            $insuranceHandler->separatePaymentNoticeForSelectedOffer($offer)
        );
    }

    public function updatedInsurancePaymentData(mixed $value, string $key): void
    {
        $this->insurancePaymentData[$key] = $this->normalizePaymentFieldValue($key, $value);

        $this->storeInsurancePaymentData();

        $this->resetValidation("insurancePaymentData.$key");
    }

    /**
     * @param  array<string, mixed>  $paymentData
     * @return array<string, string|null>
     */
    private function paymentDataForFields(array $paymentData): array
    {
        $data = [];

        foreach ($this->insurancePaymentFields as $field) {
            $key = $field['key'] ?? null;
            if (! is_string($key) || $key === '') {
                continue;
            }

            $data[$key] = isset($paymentData[$key]) && is_string($paymentData[$key])
                ? $paymentData[$key]
                : null;
        }

        return $data;
    }

    private function loadInsurancePaymentFields(): void
    {
        $this->insurancePaymentFields = collect(resolve(InsuranceHandler::class)->getPaymentFields())
            ->map(fn (InsurancePaymentFieldDto $field): array => $field->toArray())
            ->values()
            ->all();

        $this->requiresInsurancePaymentData = collect($this->insurancePaymentFields)
            ->contains(fn (array $field): bool => (bool) ($field['required'] ?? false));
    }

    private function storeInsurancePaymentData(): void
    {
        $payment = collect($this->paymentDataForFields($this->insurancePaymentData))
            ->filter(fn (?string $value): bool => $value !== null && $value !== '')
            ->all();

        $bucket = $this->insuranceBucketWithCreateOfferContext();
        $bucket[InsuranceCheckoutData::PAYMENT] = $payment === [] ? null : $payment;
        $this->model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate($bucket));
    }

    /**
     * @return array<string, mixed>
     */
    private function insuranceBucketWithCreateOfferContext(): array
    {
        $this->model->refresh();

        $checkoutArr = InsuranceCheckoutData::checkoutDataArray($this->model->data);
        $bucket = InsuranceCheckoutData::getNormalizedInsuranceBucket($checkoutArr)
            ?? InsuranceCheckoutData::emptyInsuranceBucket();

        if (! is_array($bucket[InsuranceCheckoutData::CREATE_OFFER] ?? null)) {
            $bucket[InsuranceCheckoutData::CREATE_OFFER] = $this->createOfferContext()->toArray();
        }

        return $bucket;
    }

    private function createOfferContext(): CreateInsuranceOffersDto
    {
        return new CreateInsuranceOffersDto(
            startDate: $this->itinerary->startDate->toImmutable(),
            endDate: $this->itinerary->endDate->toImmutable(),
            totalPrice: $this->itinerary->price->showTotalPrice,
            contact: $this->model->getContact(),
            paxInfo: $this->model->getPaxInfo(),
            destinationCountries: $this->itinerary->destinationCountries instanceof Collection
                ? $this->itinerary->destinationCountries
                : collect($this->itinerary->destinationCountries),
        );
    }

    private function normalizePaymentFieldValue(string $key, mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $field = $this->paymentField($key);
        $type = is_array($field) ? (string) ($field['type'] ?? 'text') : 'text';

        $clean = match ($type) {
            'iban' => preg_replace('/[^A-Z0-9]/', '', strtoupper(preg_replace('/\s+/', '', $value) ?? '')) ?? '',
            'card_number' => preg_replace('/\D+/', '', $value) ?? '',
            default => trim($value),
        };

        return $clean !== '' ? $clean : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function paymentField(string $key): ?array
    {
        foreach ($this->insurancePaymentFields as $field) {
            if (($field['key'] ?? null) === $key) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.insurance-section');
    }

    /**
     * Handle the insurance quote.
     *
     * @param  array<string, mixed>|null  $quote
     */
    public function handleInsuranceQuote(?array $quote): void
    {
        if (is_null($quote)) {
            $this->model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate(null));
            $this->dispatch('insurance-declined');

            return;
        }

        $this->insuranceSelected = true;

        $offer = new InsuranceOfferDto(
            id: $quote['quote_id'],
            title: $quote['product']['promotional_header'],
            price: new Price(amount: $quote['total'] / 100, currency: $quote['currency']),
            coverage: []
        );

        $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
        $bucket[InsuranceCheckoutData::META] = $quote;
        $bucket[InsuranceCheckoutData::OFFER] = $offer->toArray();
        $this->model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate($bucket));
        $this->dispatch('insurance-selected', new InsuranceItem($offer->id, $offer->title), $offer->price);
    }

    /**
     * Listen for the additional service section, expanding the insurance section.
     */
    #[On(Section::AdditionalService->value)]
    public function listen(): void
    {
        if (! $this->isInsuranceAvailable) {
            $this->next();

            return;
        }

        $this->expand(Section::Insurance);

        if (Config::boolean('checkout.insurance.vertical.active')) {
            $this->contact = ContactInfoPayloadEntity::from($this->model->data['contact']);

            $this->dispatch('insurance-reset-ui');
            $this->shouldInitVerticalWidget = true;
            $this->isLoadingOffers = true;

            return;
        }

        $this->isLoadingOffers = true;
        $this->offers = [];
        $this->selectedOfferId = null;
        $this->insuranceSelected = false;
        $this->insuranceProviderIsAvailable = null;

        $this->expand(Section::Insurance);

        $this->dispatch('insurance-load-offers');
        $this->dispatch('insurance-declined');
    }

    public function initVerticalWidget(): void
    {
        if (! Config::boolean('checkout.insurance.vertical.active') || ! $this->shouldInitVerticalWidget) {
            return;
        }

        if (! $this->contact && isset($this->model->data['contact'])) {
            $this->contact = ContactInfoPayloadEntity::from($this->model->data['contact']);
        }

        $this->updateAvailability();
        $this->dispatch('insurance-config-updated', config: $this->getVerticalInsuranceConfigProperty());
        $this->shouldInitVerticalWidget = false;
        $this->isLoadingOffers = false;
    }

    public function loadOffer(): void
    {
        $this->updateAvailability();

        if (AvailabilityFacade::getCachedStatus($this->getParams()) === 200) {
            $this->generateInsuranceOffers();
        } else {
            $this->insuranceProviderIsAvailable = null;
        }

        $this->isLoadingOffers = false;
    }

    public function updateAvailability(): void
    {
        (new VerifyAvailabilityJob($this->getParams()))->handle();

        if (AvailabilityFacade::getCachedStatus($this->getParams()) === 200) {
            $this->itinerary->price = AvailabilityFacade::getCachedResultDto($this->getParams())->summary->prices;
        }
    }

    /**
     * Go to the next section.
     */
    public function next(): void
    {
        if (Config::boolean('checkout.insurance.vertical.active')) {
            $this->markAsCompletedAdnCollapse(Section::Insurance);
            $this->dispatch(Section::Insurance->value);

            return;
        }

        if ($this->requiresInsurancePaymentData && $this->selectedOfferId !== null) {
            if (! $this->validateInsurancePaymentData()) {
                return;
            }

            $this->storeInsurancePaymentData();
        }

        $this->markAsCompletedAdnCollapse(Section::Insurance);

        $this->dispatch(Section::Insurance->value);
    }

    private function validateInsurancePaymentData(): bool
    {
        foreach ($this->insurancePaymentFields as $field) {
            $key = $field['key'] ?? null;
            if (! is_string($key) || $key === '') {
                continue;
            }

            $value = $this->normalizePaymentFieldValue($key, $this->insurancePaymentData[$key] ?? null);
            $this->insurancePaymentData[$key] = $value;

            if (($field['required'] ?? false) && ($value === null || $value === '')) {
                $this->addError(
                    "insurancePaymentData.$key",
                    (string) ($field['requiredMessage'] ?? trans('checkout::page.trip_details.insurance_booking_missing_payment_details'))
                );

                return false;
            }

            if (($field['type'] ?? null) === 'iban' && $value !== null && ! (new Iban)->isValid($value)) {
                $this->addError(
                    "insurancePaymentData.$key",
                    (string) ($field['invalidMessage'] ?? trans('checkout::page.trip_details.insurance_iban_validation_invalid'))
                );

                return false;
            }
        }

        return true;
    }

    /**
     *  Get the vertical insurance config.
     *
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function getVerticalInsuranceConfigProperty(): array
    {
        if (! $this->isInsuranceAvailable || ! $this->contact || ! $this->isExpanded) {
            return [];
        }

        return [
            'client_id' => config()->string('checkout.insurance.vertical.username'),
            'product_config' => [
                'travel' => [[
                    'customer' => [
                        'first_name' => $this->contact->firstName,
                        'last_name' => $this->contact->lastName,
                        'email_address' => $this->contact->email,
                        'street' => $this->contact->address->street1.' '.$this->contact->address->street2,
                        'city' => $this->contact->address->city,
                        'postal_code' => $this->contact->address->postalCode,
                        'country' => str($this->contact->address->country)->beforeLast('-')->toString(),
                    ],
                    'attributes' => [
                        'birth_date' => $this->model->refresh()->getPaxInfo()->first()->birthDate->toDateString(),
                        'trip_start_date' => $this->itinerary->startDate->toDateString(),
                        'trip_end_date' => $this->itinerary->endDate->toDateString(),
                        'destination_countries' => $this->itinerary->destinationCountries,
                        'trip_cost' => $this->itinerary->price->showTotalPrice->toCent(),
                        'trip_cost_currency' => $this->itinerary->price->showTotalPrice->currency,
                    ],
                    'currency' => $this->itinerary->price->showTotalPrice->currency,
                ]],
            ],
        ];
    }

    public function generateInsuranceOffers(): void
    {
        if ($this->model->isCompleted(Section::Contact) && $this->model->isCompleted(Section::Traveller)) {
            $offers = resolve(InsuranceHandler::class)->createOffers($this->model, $this->itinerary);
            if ($offers->isSuccessful) {
                $this->offers = $offers->offers;
                $this->insuranceProviderIsAvailable = true;
            } else {
                $this->insuranceProviderIsAvailable = false;
                $this->notAvailableMessage = $offers->errorMessage ?? $this->notAvailableMessage;
            }
        }
    }

    /**
     * Reset the section.
     *
     * @param  array<int, string>  $sections
     */
    #[On('sections-reset')]
    public function resetSection(array $sections): void
    {
        if (! in_array(Section::Insurance->value, $sections, true)) {
            return;
        }

        $this->isCompleted = false;
        $this->isExpanded = false;
        $this->offers = [];
        $this->selectedOfferId = null;
        $this->insuranceSelected = false;
        $this->insuranceProviderIsAvailable = null;
        $this->isLoadingOffers = false;
        $this->shouldInitVerticalWidget = false;
        $this->insurancePaymentData = [];
        $this->insurancePaymentFields = [];
        $this->requiresInsurancePaymentData = false;
        $this->model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate(null));
    }
}
