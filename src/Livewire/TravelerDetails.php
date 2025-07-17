<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;
use Nezasa\Checkout\Dtos\View\ShowTraveller;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;

class TravelerDetails extends Component
{
    /**
     * The PaxAllocationResponseEntity that holds the allocation of travelers.
     */
    public PaxAllocationResponseEntity $allocatedPax;

    /**
     * An array to hold the ShowTraveller objects for each room.
     *
     * @var array<int, ShowTraveller>
     */
    public array $showTravellers = [];

    /**
     * Indicates whether the traveler details section is expanded or not..
     */
    public $travelerExpanded = true;

    public function mount()
    {
        foreach ($this->allocatedPax->rooms as $number => $room) {
            for ($i = 0; $i < $room->adults; $i++) {
                $this->showTravellers[$number][] = new ShowTraveller(adult: true, show: $i === 0);
            }

            foreach ($room->childAges as $age) {
                $this->showTravellers[$number][] = new ShowTraveller(adult: false, show: false, age: $age);
            }
        }

    }

    public function save()
    {
        // No validation for now
        $this->travelerExpanded = false;
        $this->dispatch('enablePromoCodeSection');
    }

    public function render()
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
}
