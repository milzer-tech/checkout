<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Dtos\View\ShowTraveller;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PassengerRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;
use Nezasa\Checkout\Models\Checkout;

class TravelerDetails extends Component
{
    /**
     * The unique identifier for the checkout process.
     */
    #[Url]
    public string $checkoutId;

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
     */
    public array $paxInfo = [];

    /**
     * Indicates whether the traveler details section is expanded or not..
     */
    public bool $travelerExpanded = false;

    /**
     * Indicates whether the traveler details have been completed.
     */
    public bool $isCompleted = false;

    /**
     * The country calling codes for the contact details.
     */
    public CountryCodesResponse $countryCodes;

    /**
     * The countries response that holds the list of countries.
     */
    public CountriesResponse $countriesResponse;

    public function mount()
    {
        $paxInfo = Checkout::query()
            ->firstOrCreate(['checkout_id' => $this->checkoutId])
            ->data
            ?->get('paxInfo') ?? [];

        $this->setUpPaxData($paxInfo);
        $this->setShowingTravellers();
    }

    public function save()
    {
        $this->travelerExpanded = false;
        $this->isCompleted = true;

        $this->dispatch('enablePromoCodeSection');
    }

    public function render(): View
    {
        return view('checkout::trip-details-page.traveler-details');
    }

    public function showNextTraveller(string $item): void
    {
        [$room, $traveler] = $this->getRoomAndTravellerNumber($item);

        $this->validateTravellerData($room, $traveler);

        $this->paxInfo[$room][$traveler]['showTraveller']->isFilled = true;
        $this->paxInfo[$room][$traveler]['showTraveller']->isShowing = false;

        SaveTraverDetailsJob::dispatch(
            $this->checkoutId, "paxInfo.$room.$traveler", $this->paxInfo[$room][$traveler]
        );

        $traveler = $traveler + 1;
        $nextRoom = $room + 1;

        if (isset($this->paxInfo[$room][$traveler])) {
            $this->paxInfo[$room][$traveler]['showTraveller']->isFilled = false;
            $this->paxInfo[$room][$traveler]['showTraveller']->isShowing = true;

            SaveTraverDetailsJob::dispatch(
                $this->checkoutId, "paxInfo.$room.$traveler", $this->paxInfo[$room][$traveler]
            );
        } elseif (isset($this->paxInfo[$nextRoom])) {
            $this->paxInfo[$nextRoom][0]['showTraveller']->isFilled = false;
            $this->paxInfo[$nextRoom][0]['showTraveller']->isShowing = true;

            SaveTraverDetailsJob::dispatch(
                $this->checkoutId, "paxInfo.$nextRoom.0", $this->paxInfo[$nextRoom][0]
            );
        } else {
            $this->travelerExpanded = false;
            $this->dispatch('travellers-stored');
        }
    }

    public function showPreviousTraveller(string $item): void
    {
        [$room, $traveler] = $this->getRoomAndTravellerNumber($item);

        $this->paxInfo[$room][$traveler]['showTraveller']->isShowing = false;

        if ($traveler > 0) {
            $traveler = $traveler - 1;
        } else {
            $room = $room - 1;
            $traveler = count($this->paxInfo[$room]) - 1;
        }

        $this->paxInfo[$room][$traveler]['showTraveller']->isShowing = true;
    }

    #[On('contact-stored')]
    public function editTraveler(): void
    {
        $this->travelerExpanded = true;

        $this->render();
    }

    /**
     * Update the contact details when a field is changed.
     */
    public function updated(string $name, mixed $value)
    {
        $key = str($name)->after('.')
            ->after('.')
            ->after('.')
            ->prepend('paxInfo.*.*.')
            ->toString();

        $this->validate([
            $name => $this->rules()[$key],
        ]);

        SaveTraverDetailsJob::dispatch($this->checkoutId, $name, $value);
    }

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
            'passportExpirationDate.year' => ['integer', 'min:1900', 'max:'.date('Y')],
            'passportIssuingCountry' => ['string'], // country response
            'postalCode' => ['string', 'max:20'],
            'city' => ['string', 'max:255'],
            'country' => ['string', 'max:255'],
            'countryCode' => ['string', 'max:10'],
            'street1' => ['string', 'max:255'],
            'street2' => ['string', 'max:255'],
        ];

        foreach ($this->passengerRequirements as $name => $item) {
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
            array_map(fn ($key) => 'paxInfo.*.*.'.$key, array_keys($rules)),
            array_values($rules)
        );
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function getRoomAndTravellerNumber(string $item): array
    {
        return str($item)->explode('-')->transform(fn ($item) => intval($item))->toArray();
    }

    protected function validateTravellerData(int $room, int $travelerNumber): void
    {
        $this->validate(
            collect($this->rules())->mapWithKeys(fn (array $rule, string $key) => [
                str($key)->replaceFirst('*', $room)->replaceFirst('*', $travelerNumber)->toString() => $rule,
            ])->all()
        );
    }

    protected function setUpPaxData(array $paxInfo): void
    {
        $paxNumber = 0;

        foreach ($this->allocatedPax->rooms as $number => $room) {
            for ($i = 0; $i < $room->adults; $i++) {

                $this->paxInfo[$number][$i] = $paxInfo[$number][$i] ?? [];
                if (! isset($this->paxInfo[$number][$i]['showTraveller'])) {
                    $this->paxInfo[$number][$i]['showTraveller'] = new ShowTraveller(isAdult: true);
                } else {
                    $this->paxInfo[$number][$i]['showTraveller'] = ShowTraveller::from($paxInfo[$number][$i]['showTraveller']);
                }

                $this->paxInfo[$number][$i]['refId'] = "pax-$paxNumber";

                $paxNumber++;
            }

            foreach ($room->childAges as $index => $age) {
                $this->paxInfo[$number][$index + $i] = $paxInfo[$number][$index + $i] ?? [];

                if (! isset($this->paxInfo[$number][$index + $i]['showTraveller'])) {
                    $this->paxInfo[$number][$index + $i]['showTraveller'] = new ShowTraveller(isAdult: false, age: $age);
                } else {
                    $this->paxInfo[$number][$index + $i]['showTraveller'] = ShowTraveller::from($paxInfo[$number][$index + $i]['showTraveller']);
                    $this->paxInfo[$number][$index + $i]['showTraveller']->age = $age;
                }

                $this->paxInfo[$number][$index + $i]['refId'] = "pax-$paxNumber";

                $paxNumber++;
            }
        }
    }

    protected function setShowingTravellers(): void
    {
        foreach ($this->paxInfo as $roomNumber => $room) {
            if (collect($this->paxInfo[$roomNumber])
                ->pluck('showTraveller')
                ->filter(fn (ShowTraveller $item) => $item->isShowing)
                ->isNotEmpty()
            ) {
                return;
            }
        }

        $this->paxInfo[0][0]['showTraveller']->isShowing = true;
    }

    /**
     * Returns the validation messages for the contact details.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return collect($this->rules())
            ->reject(fn ($item, $key) => $key === 'paxInfo.*.*.address1' || $key === 'paxInfo.*.*.address2')
            ->mapWithKeys(function ($item, $key) {
                $translatedKey = str($key)->afterLast('.')->toString();

                return [$key => strtolower(trans("checkout::input.attributes.$translatedKey"))];
            })
            ->toArray();
    }
}
