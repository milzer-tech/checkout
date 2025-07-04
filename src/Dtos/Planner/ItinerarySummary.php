<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryActivity;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryFlight;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryRentalCar;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryStay;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryTransfer;

class ItinerarySummary extends BaseDto
{
    /**
     * The number of nights in the itinerary.
     */
    public int $nights;

    /**
     * Create a new instance of the ItinerarySummary.
     *
     * @param  Collection<int, int>|array<int, int>  $childrenAges
     * @param  Collection<int, ItineraryStay>  $stays
     * @param  Collection<int, ItineraryFlight>  $flights
     * @param  Collection<int, ItineraryTransfer>  $transfers
     * @param  Collection<int, ItineraryActivity>  $activities
     * @param  Collection<int, ItineraryRentalCar>  $rentalCars
     */
    public function __construct(
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
        public int $adults,
        public int $children = 0,
        public Collection|array $childrenAges = new Collection,
        public Collection $stays = new Collection,
        public Collection $flights = new Collection,
        public Collection $transfers = new Collection,
        public Collection $activities = new Collection,
        public Collection $rentalCars = new Collection
    ) {
        $this->nights = (int) $this->startDate->diffInDays($this->endDate);

        if (is_array($this->childrenAges)) {
            $this->childrenAges = new Collection($this->childrenAges);
        }
    }
}
