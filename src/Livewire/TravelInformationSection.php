<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\On;
use Nezasa\Checkout\Actions\TravelInformation\LoadTravelInformationAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Dtos\TravelInformation\TravelInformationCombination;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RegulatoryInformationResponse;

class TravelInformationSection extends BaseCheckoutComponent
{
    public ItinerarySummary $itinerary;

    public RegulatoryInformationResponse $regulatoryInformation;

    public bool $travelInformationConfirmed = false;

    /**
     * @var array<int, array<string, string|null>>
     */
    public array $combinations = [];

    public function mount(LoadTravelInformationAction $loadTravelInformationAction): void
    {
        if ($this->shouldRender()) {
            $this->syncTravelInformationConfirmation($loadTravelInformationAction);
        }

        if ($this->shouldRender() && ($this->isExpanded || $this->isCompleted || $this->model->isCompleted(Section::TermsAndConditions))) {
            $this->loadCombinations($loadTravelInformationAction);
        }
    }

    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.travel-information-section');
    }

    public function shouldRender(): bool
    {
        return $this->regulatoryInformation->travelInformation?->confirmationEnabled === true
            && Config::boolean('checkout.integrations.passolution.active')
            && filled(Config::string('checkout.integrations.passolution.token'));
    }

    public function toggleTravelInformationConfirmation(bool $value): void
    {
        $this->travelInformationConfirmed = $value;
        $this->model->updateData([
            'travel_information_confirmed' => $value,
            'travel_information_confirmation_hash' => $value
                ? resolve(LoadTravelInformationAction::class)->confirmationHash($this->model, $this->itinerary->destinationCountries)
                : null,
        ]);

        if ($value) {
            $this->resetValidation('travelInformationConfirmed');
        }
    }

    public function next(): void
    {
        if (! $this->shouldRender()) {
            $this->markAsCompletedAdnCollapse(Section::TravelInformation);
            $this->dispatch(Section::TravelInformation->value);

            return;
        }

        $this->validate([
            'travelInformationConfirmed' => ['required', 'accepted'],
        ]);

        $this->model->updateData([
            'travel_information_confirmed' => true,
            'travel_information_confirmation_hash' => resolve(LoadTravelInformationAction::class)
                ->confirmationHash($this->model, $this->itinerary->destinationCountries),
        ]);
        $this->markAsCompletedAdnCollapse(Section::TravelInformation);
        $this->dispatch(Section::TravelInformation->value);
    }

    #[On(Section::TermsAndConditions->value)]
    public function listen(LoadTravelInformationAction $loadTravelInformationAction): void
    {
        if (! $this->shouldRender()) {
            $this->next();

            return;
        }

        $this->syncTravelInformationConfirmation($loadTravelInformationAction);
        $this->loadCombinations($loadTravelInformationAction);
        $this->expand(Section::TravelInformation);
    }

    /**
     * @param  array<int, string>  $sections
     */
    #[On('sections-reset')]
    public function resetSection(array $sections): void
    {
        if (! in_array(Section::TravelInformation->value, $sections, true)) {
            return;
        }

        $this->isCompleted = false;
        $this->isExpanded = false;
    }

    private function loadCombinations(LoadTravelInformationAction $loadTravelInformationAction): void
    {
        $this->combinations = $loadTravelInformationAction
            ->run($this->model, $this->itinerary->destinationCountries, $this->lang ?? 'en')
            ->map(fn (TravelInformationCombination $combination): array => [
                'title' => $combination->title(),
                'health' => $combination->health(),
                'entry' => $combination->entry(),
                'visa' => $combination->visa(),
                'transit_visa' => $combination->transitVisa(),
            ])
            ->values()
            ->all();
    }

    private function syncTravelInformationConfirmation(LoadTravelInformationAction $loadTravelInformationAction): void
    {
        $confirmationHash = $loadTravelInformationAction->confirmationHash($this->model, $this->itinerary->destinationCountries);
        $storedConfirmationHash = data_get($this->model->data, 'travel_information_confirmation_hash');

        if ($storedConfirmationHash !== $confirmationHash) {
            $this->model->updateData([
                'travel_information_confirmed' => false,
                'travel_information_confirmation_hash' => null,
                'status.'.Section::TravelInformation->value.'.isCompleted' => false,
            ]);
            $this->isCompleted = false;
        }

        $this->travelInformationConfirmed = (bool) data_get($this->model->refresh()->data, 'travel_information_confirmed', false);
    }
}
