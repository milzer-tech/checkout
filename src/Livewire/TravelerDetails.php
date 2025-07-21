<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Dtos\View\ShowTraveller;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PassengerRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
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
     * An array to hold the ShowTraveller objects for each room.
     *
     * @var array<int, ShowTraveller>
     */
    public array $showTravellers = [];

    /**
     * An array to hold the information of each traveler.
     */
    public array $paxInfo = [];

    /**
     * Indicates whether the traveler details section is expanded or not..
     */
    public $travelerExpanded = true;

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
            ?->get('paxInfo');

        foreach ($this->allocatedPax->rooms as $number => $room) {
            for ($i = 0; $i < $room->adults; $i++) {
                $this->showTravellers[$number][$i] = new ShowTraveller(adult: true, show: $i === 0);

                $this->paxInfo[$number][$i] = $paxInfo[$number][$i] ?? [];
            }

            foreach ($room->childAges as $index => $age) {
                $this->showTravellers[$number][$index + $i] = new ShowTraveller(adult: false, show: false, age: $age);
                $this->paxInfo[$number][$index + $i] = [];
                $this->paxInfo[$number][$index + $i] = $paxInfo[$number][$index + $i] ?? [];
            }
        }
    }

    public function save()
    {
        $this->travelerExpanded = false;
        $this->dispatch('enablePromoCodeSection');
    }

    public function render(): View
    {
        return view('checkout::trip-details-page.traveler-details');
    }

    public function showNextTraveller(string $item): void
    {
        [$room, $traveler] = str($item)->explode('-')->transform(fn ($item) => intval($item));

        $this->showTravellers[$room][$traveler]->show = false;

        if (isset($this->showTravellers[$room][$traveler + 1])) {
            $this->showTravellers[$room][$traveler + 1]->show = true;
        } else {
            $this->showTravellers[$room][0]->show = true;
        }
    }

    /**
     * Show the details of a specific traveler.
     */
    public function showTraveller(string $item): void
    {
        [$room, $traveler] = str($item)->explode('-')->transform(fn ($item) => intval($item));

        foreach ($this->showTravellers[$room] as $item) {
            $item->show = false;
        }

        $this->showTravellers[$room][$traveler]->show = true;
    }

    public function editTraveler(): void
    {
        $this->travelerExpanded = true;
    }

    /**
     * Update the contact details when a field is changed.
     */
    public function updated(string $name, mixed $value)
    {
        Checkout::query()
            ->firstOrCreate(['checkout_id' => $this->checkoutId])
            ->updateData($name, $value);
    }
}
