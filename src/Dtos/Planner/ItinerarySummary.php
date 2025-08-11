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
            ->filter(fn (ItineraryTransfer $transfer) => ! $transfer->isPlaceholder)
            ->isNotEmpty();
    }

    /**
     * Check if the itinerary flights are displayable.
     */
    public function hasFlights(): bool
    {
        return $this->flights
            ->filter(fn (ItineraryFlight $flight) => ! $flight->isPlaceholder)
            ->isNotEmpty();
    }

    /**
     * Check if the itinerary rental cars are displayable.
     */
    public function hasRentalCar(): bool
    {
        return $this->rentalCars
            ->filter(fn (ItineraryRentalCar $rentalCar) => ! $rentalCar->isPlaceholder)
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
     * Check if all hotels in the itinerary are available.
     */
    public function areAllHotelsAvailable(): bool
    {
        return $this->stays
            ->reject(fn (ItineraryStay $item) => $item->availability?->isOpen())
            ->isEmpty();
    }

    public function getHotelsGroupStatus(): ?AvailabilityEnum
    {
        return $this->areAllHotelsAvailable()
            ? AvailabilityEnum::Open
            : $this->stays
                ->firstWhere(fn (ItineraryStay $item) => ! $item->availability?->isOpen())
                ->availability;
    }

    /**
     * Check if all activities in the itinerary are available.
     */
    public function areAllActivitiesAvailable(): bool
    {
        return $this->activities
            ->reject(fn (ItineraryActivity $item) => $item->availability?->isOpen())
            ->isEmpty();
    }

    public function getActivitiesGroupStatus(): ?AvailabilityEnum
    {
        return $this->areAllActivitiesAvailable()
            ? AvailabilityEnum::Open
            : $this->activities
                ->firstWhere(fn (ItineraryActivity $item) => ! $item->availability?->isOpen())
                ->availability;
    }

    /**
     * Check if all transfers in the itinerary are available.
     */
    public function areAllTransfersAvailable(): bool
    {
        return $this->transfers
            ->reject(fn (ItineraryTransfer $item) => $item->availability?->isOpen())
            ->isEmpty();
    }

    public function getTransfersGroupStatus(): ?AvailabilityEnum
    {
        return $this->areAllTransfersAvailable()
            ? AvailabilityEnum::Open
            : $this->transfers
                ->firstWhere(fn (ItineraryTransfer $item) => ! $item->availability?->isOpen())
                ->availability;
    }

    /**
     * Check if all flights in the itinerary are available.
     */
    public function areAllFlightsAvailable(): bool
    {
        return $this->flights
            ->reject(fn (ItineraryFlight $item) => $item->availability?->isOpen())
            ->isEmpty();
    }

    public function getFlightsGroupStatus(): ?AvailabilityEnum
    {
        return $this->areAllFlightsAvailable()
            ? AvailabilityEnum::Open
            : $this->flights
                ->firstWhere(fn (ItineraryFlight $item) => ! $item->availability?->isOpen())
                ->availability;
    }

    /**
     * Check if all rental cars in the itinerary are available.
     */
    public function areAllRentalCarsAvailable(): bool
    {
        return $this->rentalCars
            ->reject(fn (ItineraryRentalCar $item) => $item->availability?->isOpen())
            ->isEmpty();
    }

    public function rentalCarGroupStatus(): ?AvailabilityEnum
    {
        return $this->areAllRentalCarsAvailable()
            ? AvailabilityEnum::Open
            : $this->rentalCars
                ->firstWhere(fn (ItineraryRentalCar $car) => ! $car->availability?->isOpen())
                ->availability;
    }

    /**
     * Check if all upsell items in the itinerary are available.
     */
    public function areAllUpsellItemsAvailable(): bool
    {
        return $this->upsellItems
            ->reject(fn (UpsellItem $item) => $item->availability?->isOpen())
            ->isEmpty();
    }

    public function getUpsellItemsGroupStatus(): ?AvailabilityEnum
    {
        return $this->areAllUpsellItemsAvailable()
            ? AvailabilityEnum::Open
            : $this->upsellItems
                ->firstWhere(fn (UpsellItem $item) => ! $item->availability?->isOpen())
                ->availability;
    }
}
