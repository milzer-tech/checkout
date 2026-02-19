<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryActivity;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryFlight;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryRentalCar;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryStay;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryTransfer;
use Nezasa\Checkout\Dtos\Planner\Entities\UpsellItem;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TermsAndConditionsResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;

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
     * @param  Collection<int, UpsellItem>  $upsellItems
     * @param  Collection<int, InsuranceItem>  $insurances
     * @param  Collection<int, string>|array<int, string>  $destinationCountries
     */
    public function __construct(
        public PriceResponse $price,
        public string $title,
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
        public int $adults,
        public int $children = 0,
        public Collection|array $childrenAges = new Collection,
        public Collection $stays = new Collection,
        public Collection $flights = new Collection,
        public Collection $transfers = new Collection,
        public Collection $activities = new Collection,
        public Collection $rentalCars = new Collection,
        public Collection $upsellItems = new Collection,
        public TermsAndConditionsResponseEntity $termsAndConditions = new TermsAndConditionsResponseEntity,
        public Collection $insurances = new Collection,
        public Collection|array $destinationCountries = new Collection
    ) {
        $this->nights = (int) $this->startDate->diffInDays($this->endDate);

        if (is_array($this->childrenAges)) {
            $this->childrenAges = new Collection($this->childrenAges);
        }

        if (is_array($this->destinationCountries)) {
            $this->destinationCountries = new Collection($this->destinationCountries);
        }
    }

    /**
     * Check if the itinerary transfers are displayable.
     */
    public function hasTransfers(): bool
    {
        return $this->transfers
            ->filter(fn (ItineraryTransfer $transfer): bool => ! $transfer->isPlaceholder)
            ->isNotEmpty();
    }

    /**
     * Check if the itinerary flights are displayable.
     */
    public function hasFlights(): bool
    {
        return $this->flights
            ->filter(fn (ItineraryFlight $flight): bool => ! $flight->isPlaceholder)
            ->isNotEmpty();
    }

    /**
     * Check if the itinerary rental cars are displayable.
     */
    public function hasRentalCar(): bool
    {
        return $this->rentalCars
            ->filter(fn (ItineraryRentalCar $rentalCar): bool => ! $rentalCar->isPlaceholder)
            ->isNotEmpty();
    }

    /**
     * Check if the itinerary has upsell items.
     */
    public function hasUpsellItem(): bool
    {
        return $this->upsellItems->isNotEmpty();
    }

    /**
     * Check if the itinerary has insurance items.
     */
    public function hasInsurance(): bool
    {
        return $this->insurances->isNotEmpty();
    }

    public function getHotelsGroupStatus(): ?AvailabilityEnum
    {
        $count = $this->stays->count();

        $grouped = $this->stays->groupBy('availability');

        if ($grouped->isNotEmpty() && $grouped->first()->count() === $count) {
            return $this->stays->first()->availability;
        }

        return null;
    }

    public function getActivitiesGroupStatus(): ?AvailabilityEnum
    {
        $count = $this->activities->count();

        $grouped = $this->activities->groupBy('availability');

        if ($grouped->isNotEmpty() && $grouped->first()->count() === $count) {
            return $this->activities->first()->availability;
        }

        return null;
    }

    public function getTransfersGroupStatus(): ?AvailabilityEnum
    {
        $count = $this->transfers->count();

        $grouped = $this->transfers->groupBy('availability');

        if ($grouped->isNotEmpty() && $grouped->first()->count() === $count) {
            return $this->transfers->first()->availability;
        }

        return null;
    }

    public function getFlightsGroupStatus(): ?AvailabilityEnum
    {
        $count = $this->flights->count();

        $grouped = $this->flights->groupBy('availability');

        if ($grouped->isNotEmpty() && $grouped->first()->count() === $count) {
            return $this->flights->first()->availability;
        }

        return null;
    }

    public function rentalCarGroupStatus(): ?AvailabilityEnum
    {
        $count = $this->rentalCars->count();

        $grouped = $this->rentalCars->groupBy('availability');

        if ($grouped->isNotEmpty() && $grouped->first()->count() === $count) {
            return $this->rentalCars->first()->availability;
        }

        return null;
    }

    public function getUpsellItemsGroupStatus(): ?AvailabilityEnum
    {
        $count = $this->upsellItems->count();

        $grouped = $this->upsellItems->groupBy('availability');

        if ($grouped->isNotEmpty() && $grouped->first()->count() === $count) {
            return $this->upsellItems->first()->availability;
        }

        return null;
    }

    /**
     * Returns the unconfirmed stays.
     *
     * @return Collection<int, ItineraryStay>
     */
    public function getUnconfirmedStays(): Collection
    {
        return $this->stays->reject(fn (ItineraryStay $stay) => $stay->availability?->isBooked());
    }

    /**
     * Returns the unconfirmed activities.
     *
     * @return Collection<int, ItineraryActivity>
     */
    public function getUnconfirmedActivities(): Collection
    {
        return $this->activities->reject(fn (ItineraryActivity $activity) => $activity->availability?->isBooked());
    }

    /**
     * Returns the unconfirmed flights.
     *
     * @return Collection<int, ItineraryFlight>
     */
    public function getUnconfirmedFlights(): Collection
    {
        return $this->flights->reject(
            fn (ItineraryFlight $flight) => $flight->availability?->isBooked() || $flight->isPlaceholder
        );
    }

    /**
     * Returns the unconfirmed transfers.
     *
     * @return Collection<int, ItineraryTransfer>
     */
    public function getUnconfirmedTransfers(): Collection
    {
        return $this->transfers->reject(
            fn (ItineraryTransfer $transfer) => $transfer->availability?->isBooked() || $transfer->isPlaceholder
        );
    }

    /**
     * Returns the unconfirmed rental cars.
     *
     * @return Collection<int, ItineraryRentalCar>
     */
    public function getUnconfirmedRentalCars(): Collection
    {
        return $this->rentalCars->reject(
            fn (ItineraryRentalCar $car) => $car->availability?->isBooked() || $car->isPlaceholder
        );
    }

    /**
     * Get the unconfirmed upsell items.
     *
     * @return Collection<int, UpsellItem>
     */
    public function getUnconfirmedUpsellItems(): Collection
    {
        return $this->upsellItems->reject(fn (UpsellItem $upsellItem) => $upsellItem->availability?->isBooked());
    }
}
