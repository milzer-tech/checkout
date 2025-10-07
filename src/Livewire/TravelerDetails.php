<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\On;
use Nezasa\Checkout\Actions\Checkout\TravellerDetails\TravellerSupporter;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PassengerRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;

class TravelerDetails extends BaseCheckoutComponent
{
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
     * Mount the component and initialize the traveler details.
     */
    public function mount(): void
    {
        /** @phpstan-ignore-next-line */
        $paxInfo = $this->model->data->get('paxInfo');
        $this->paxInfo = TravellerSupporter::setUpPaxData($this->allocatedPax, $paxInfo);
        $this->paxInfo = TravellerSupporter::setShowingTravellers($this->paxInfo);
        $this->updateFormStatus();
    }

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
    #[On('contact-processed')]
    public function listen(): void
    {
        $this->isCompleted ? $this->dispatch('traveller-processed') : $this->expand(Section::Traveller);
    }

    /**
     * Save the traveler details and mark the section as completed.
     */
    public function save(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Traveller);

        $this->dispatch('traveller-processed');
    }

    /**
     * Get the validation rules for the traveler details.
     *
     * @return array<string, array<Enum|string>>
     */
    protected function rules(): array
    {
        $rules = [
            'firstName' => ['string', 'max:255'],
            'lastName' => ['string', 'max:255'],
            'secondOrAdditionalName' => ['string', 'max:255'],
            'passportNr' => ['string', 'max:255'],
            'nationality' => ['string'], // country response
            'gender' => [new Enum(GenderEnum::class)],
            'birthDate' => ['array'],
            'birthDate.day' => ['integer', 'min:1', 'max:31'],
            'birthDate.month' => ['integer', 'min:1', 'max:12'],
            'birthDate.year' => ['integer', 'min:1900', 'max:'.date('Y')],
            'passportExpirationDate' => ['array'],
            'passportExpirationDate.day' => ['integer', 'min:1', 'max:31'],
            'passportExpirationDate.month' => ['integer', 'min:1', 'max:12'],
            'passportExpirationDate.year' => ['integer', 'min:'.date('Y'), 'max:'.date('Y') + 20],
            'passportIssuingCountry' => ['string'], // country response
            'postalCode' => ['string', 'max:20'],
            'city' => ['string', 'max:255'],
            'country' => ['string', 'max:255'],
            'countryCode' => ['string', 'max:10'],
            'street1' => ['string', 'max:255'],
            'street2' => ['string', 'max:255'],
        ];

        foreach ($this->passengerRequirements->all() as $name => $item) {
            if ($item->isRequired()) {
                $rules[$name] = array_merge(['required'], $rules[$name]);

                if ($name === 'birthDate' || $name === 'passportExpirationDate') {
                    $rules["$name.day"] = array_merge(['required'], $rules["$name.day"]);
                    $rules["$name.month"] = array_merge(['required'], $rules["$name.month"]);
                    $rules["$name.year"] = array_merge(['required'], $rules["$name.year"]);
                }
            }
        }

        return array_combine(
            array_map(fn (string $key): string => 'paxInfo.*.*.'.$key, array_keys($rules)),
            array_values($rules)
        );
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
            $this->dispatch('traveller-processed');
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

        SaveTraverDetailsJob::dispatch($this->checkoutId, $name, $value);
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
        $this->validate(collect($this->rules())->mapWithKeys(fn (array $rule, string $key) => [
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
            ->mapWithKeys(function ($item, $key) {
                $translatedKey = str($key)->afterLast('.')->toString();

                return [$key => strtolower(trans("checkout::input.attributes.$translatedKey"))];
            })
            ->toArray();
    }
}
