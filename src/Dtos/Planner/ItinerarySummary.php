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
use Nezasa\Checkout\Dtos\Planner\Entities\UpsellItem;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
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
     */
    public function __construct(
        public Price $price,
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
        public ?ApplyPromoCodeResponse $promoCodeResponse = null,
    ) {
        $this->nights = (int) $this->startDate->diffInDays($this->endDate);

        if (is_array($this->childrenAges)) {
            $this->childrenAges = new Collection($this->childrenAges);
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
}
