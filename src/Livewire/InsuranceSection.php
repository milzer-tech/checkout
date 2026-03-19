<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\On;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Facades\AvailabilityFacade;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Handlers\InsuranceHandler;
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
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(InsuranceHandler $insuranceHandler): void
    {
        $this->notAvailableMessage = trans('checkout::page.trip_details.insurance_not_available');
        $this->isInsuranceAvailable = $insuranceHandler->isAvailable();

        if (! $this->isInsuranceAvailable) {
            $this->next();

            return;
        }

        if (isset($this->model->data['contact'])) {
            $this->contact = ContactInfoPayloadEntity::from($this->model->data['contact']);
        }
    }

    public function updateSelectedOfferId(?string $id): void
    {
        $offer = collect($this->offers)->firstWhere('id', $id);

        if (is_null($offer)) {
            $this->selectedOfferId = null;
            $this->model->updateData(['insurance' => null]);
            $this->dispatch('insurance-declined');

            return;
        }

        $this->selectedOfferId = $id;
        $this->model->updateData(['insurance' => $offer->toArray()]);
        $this->dispatch('insurance-selected', new InsuranceItem($id, $offer->title), $offer->price);
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
            $this->model->updateData(['insurance_meta' => $quote, 'insurance' => null]);
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

        $this->model->updateData(['insurance_meta' => $quote, 'insurance' => $offer->toArray()]);
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
        $this->markAsCompletedAdnCollapse(Section::Insurance);

        $this->dispatch(Section::Insurance->value);
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
    }
}
