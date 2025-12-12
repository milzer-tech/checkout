<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\In;
use Livewire\Attributes\On;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PassengerRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;
use Nezasa\Checkout\Rules\BirthDateRule;
use Nezasa\Checkout\Rules\PassportExpirationDateRule;
use Nezasa\Checkout\Supporters\TravellerSupporter;
use Nezasa\Checkout\Supporters\TravelValidationsRulesSupporter;

class TravelerDetails extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * The PaxAllocationResponseEntity that holds the allocation of travelers.
     */
    public PaxAllocationResponseEntity $allocatedPax;

    /**
     * The PassengerRequirementEntity that holds the requirements for passengers.
     */
    public PassengerRequirementEntity $passengerRequirements;

    /**
     * An array to hold the information of each traveler.
     *
     * @var array<int, array<int, array<string, mixed>>>
     */
    public array $paxInfo = [];

    /**
     * The country calling codes for the contact details.
     */
    public CountryCodesResponse $countryCodes;

    /**
     * The countries response that holds the list of countries.
     */
    public CountriesResponse $countriesResponse;

    /**
     * Render the view for the traveler details page.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.traveler-details');
    }

    /**
     * Listen for the 'contact-stored' event to determine if the traveler section should be expanded or completed.
     */
    #[On(Section::Contact->value)]
    public function listen(): void
    {
        /** @phpstan-ignore-next-line */
        $paxInfo = $this->model->data->get('paxInfo');

        if ($paxInfo === []) {
            $paxInfo[0][0]['isMainContact'] = true;

            foreach ($this->passengerRequirements->getVisibleFields() as $name => $requirement) {
                if (! $requirement->isHidden() && isset($this->model->data['contact'][$name])) {
                    $paxInfo[0][0][$name] = $this->model->data['contact'][$name];
                }
            }
        }

        $this->paxInfo = TravellerSupporter::setUpPaxData($this->allocatedPax, $paxInfo, $this->countriesResponse);
        $this->paxInfo = TravellerSupporter::setShowingTravellers($this->paxInfo);

        $this->updateFormStatus();

        $this->isCompleted ? $this->dispatch(Section::Traveller->value) : $this->expand(Section::Traveller);
    }

    /**
     * Save the traveler details and mark the section as completed.
     */
    public function save(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Traveller);

        $this->dispatch(Section::Traveller->value);
    }

    /**
     * Get the validation rules for the traveler details.
     *
     * @return array<string, array<Enum|string|Rule|PassportExpirationDateRule|BirthDateRule|In>>
     */
    protected function rules(): array
    {
        return (new TravelValidationsRulesSupporter($this))->rules();
    }

    /**
     * Check if the form is completed by checking if all travelers have filled their details.
     */
    public function updateFormStatus(): void
    {
        $this->isCompleted = TravellerSupporter::isFormCompleted($this->paxInfo);
    }

    /**
     * Show the next traveler in the list.
     */
    public function showNextTraveller(string $item): void
    {
        [$room, $traveler] = $this->getRoomAndTravellerNumber($item);
        $this->validateTravellerData($room, $traveler);
        $this->paxInfo = TravellerSupporter::saveActiveTraveller($this->paxInfo, $this->checkoutId, $room, $traveler);

        $nextTraveler = $traveler + 1;
        $nextRoom = $room + 1;

        if (isset($this->paxInfo[$room][$nextTraveler])) {
            $this->paxInfo = TravellerSupporter::nextInactiveTraveller($this->paxInfo, $this->checkoutId, $room, $nextTraveler);
        } elseif (isset($this->paxInfo[$nextRoom])) {
            $this->paxInfo = TravellerSupporter::nextInactiveTraveller($this->paxInfo, $this->checkoutId, $nextRoom, 0);
        } else {
            $this->paxInfo[$room][0]['showTraveller']->isShowing = true;

            $this->updateFormStatus();
            $this->markAsCompletedAdnCollapse(Section::Traveller);
            $this->dispatch(Section::Traveller->value);
        }
    }

    /**
     * Show the previous traveler in the list.
     */
    public function showPreviousTraveller(string $item): void
    {
        [$room, $traveler] = $this->getRoomAndTravellerNumber($item);
        $this->paxInfo[$room][$traveler]['showTraveller']->isShowing = false;

        if ($traveler > 0) {
            $traveler -= 1;
        } else {
            $room -= 1;
            $traveler = count($this->paxInfo[$room]) - 1;
        }

        $this->paxInfo[$room][$traveler]['showTraveller']->isShowing = true;
    }

    /**
     * Update the traveller's details when a field is changed.
     */
    public function updated(string $name, mixed $value): void
    {
        $key = str($name)->after('.')->after('.')->after('.')->prepend('paxInfo.*.*.');

        $this->validate([$name => $this->rules()[$key->toString()]]);

        dispatch(new SaveTraverDetailsJob($this->checkoutId, $name, $value));
    }

    public function updateMainContact(string $name, mixed $value): void
    {
        $this->updated($name, $value);
    }

    /**
     * Extracts the room and traveller number from the given item string.
     *
     * @return array<mixed>
     */
    protected function getRoomAndTravellerNumber(string $item): array
    {
        return str($item)->explode('-')->transform(fn ($item): int => intval($item))->toArray();
    }

    /**
     * Validates the traveller data for a specific room and traveller number.
     */
    protected function validateTravellerData(int $room, int $travelerNumber): void
    {
        $this->validate(collect($this->rules())->mapWithKeys(fn (array $rule, string $key): array => [
            str($key)->replaceFirst('*', (string) $room)->replaceFirst('*', (string) $travelerNumber)->toString() => $rule,
        ])->all());
    }

    /**
     * Returns the validation messages for the contact details.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return collect($this->rules())
            ->reject(fn ($item, $key): bool => $key === 'paxInfo.*.*.address1' || $key === 'paxInfo.*.*.address2')
            ->mapWithKeys(function ($item, $key): array {
                $translatedKey = str($key)->afterLast('.')->toString();

                return [$key => strtolower(trans("checkout::input.attributes.$translatedKey"))];
            })
            ->toArray();
    }
}
