<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Planner;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryActivity;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryFlight;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryRentalCar;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryStay;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryTransfer;
use Nezasa\Checkout\Dtos\Planner\Entities\UpsellItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\AddedRentalCarResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\AddedUpsellItemResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\LegConnectionEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\LegResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ModulesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RentalCarResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\GetItineraryResponse as ItineraryResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RetrieveCheckoutResponse as CheckoutResponse;
use Nezasa\Checkout\Models\Checkout;
use Throwable;

class SummarizeItineraryAction
{
    /**
     * The result of the itinerary summary.
     */
    private ItinerarySummary $result;

    /**
     * Handle summarizing the itinerary by its ID.
     *
     * @param  Collection<int, AddedUpsellItemResponseEntity>  $addedUpsellItemsResponse
     *
     * @throws Throwable
     */
    public function run(
        ItineraryResponse $itineraryResponse,
        CheckoutResponse $checkoutResponse,
        AddedRentalCarResponse $addedRentalCarResponse,
        Collection $addedUpsellItemsResponse,
        Checkout $checkout
    ): ItinerarySummary {
        $this->initializeResult($itineraryResponse, $checkoutResponse);
        $this->restPaymentConfigs($checkout);
        $this->pushTransport($itineraryResponse->startConnections);
        $this->pushTransport($itineraryResponse->returnConnections);
        $this->pushRentalCar($addedRentalCarResponse->rentalCars);
        $this->pushUpsellItems($addedUpsellItemsResponse);

        foreach ($itineraryResponse->modules as $module) {
            $this->pushCountry($module);
            $this->pushTransport($module->returnConnections);

            foreach ($module->legs as $leg) {
                $this->pushAccommodation($leg);
                $this->pushActivities($leg);
                $this->pushTransport($leg->connections);
            }
        }

        /** @phpstan-ignore-next-line  destinationCountries */
        $this->result->destinationCountries = $this->result->destinationCountries->unique();

        return $this->result;
    }

    private function pushCountry(ModulesResponseEntity $module): void
    {
        if ($module->startLocation->countryCode) {
            $this->result->destinationCountries[] = $module->startLocation->countryCode;
        }

        if ($module->endLocation->countryCode) {
            $this->result->destinationCountries[] = $module->endLocation->countryCode;
        }
    }

    /**
     * Initialize the result with the start and end dates from the itinerary.
     */
    private function initializeResult(ItineraryResponse $itineraryResponse, CheckoutResponse $checkoutResponse): void
    {
        $this->result = new ItinerarySummary(
            price: $checkoutResponse->prices,
            title: $itineraryResponse->title,
            startDate: $itineraryResponse->startDate,
            endDate: $itineraryResponse->endDate,
            adults: $itineraryResponse->countAdults(),
            children: $itineraryResponse->countChildren(),
            childrenAges: $itineraryResponse->getChildrenAges(),
            termsAndConditions: $checkoutResponse->termsAndConditions,
            destinationCountries: new Collection
        );
    }

    /**
     * Apply rest payment configs to the itinerary summary.
     */
    private function restPaymentConfigs(Checkout $checkout): void
    {
        if ($checkout->rest_payment) {
            $this->result->price->showPaymentPrice = $this->result->price->openAmount;
        }
    }

    /**
     * Extract accommodations from a leg and add them to the itinerary summary.
     */
    private function pushAccommodation(LegResponseEntity $leg): void
    {
        foreach ($leg->stop->accommodations as $hotel) {
            $this->result->stays->push(
                new ItineraryStay(
                    id: $hotel->id,
                    name: $hotel->location->name,
                    checkIn: $hotel->startDate,
                    nights: $hotel->nights
                )
            );
        }
    }

    /**
     * Extract activities from a leg and add them to the itinerary summary.
     */
    private function pushActivities(LegResponseEntity $leg): void
    {
        foreach ($leg->stop->activities as $activity) {
            $this->pushActivity($activity->id, $activity->name, $activity->startDateTime, $activity->endDateTime);
        }
    }

    /**
     * Extract connection types from a leg and add them to the itinerary summary.
     *
     * @param  Collection<int, LegConnectionEntity>  $connections
     */
    private function pushTransport(Collection $connections): void
    {
        foreach ($connections as $connection) {
            if ($connection->isPlaceholder) {
                continue;
            }

            match ($connection->connectionType) {
                'Transfer' => $this->pushTransfer($connection),
                'Flight' => $this->pushFlight($connection),
                'Activity' => $this->pushActivity(
                    $connection->id,
                    $connection->name,
                    $connection->startDateTime,
                    $connection->endDateTime
                ),
                default => ''
            };
        }
    }

    /**
     * Push rental cars to the itinerary summary.
     *
     * @param  Collection<int, RentalCarResponseEntity>  $cars
     */
    private function pushRentalCar(Collection $cars): void
    {
        foreach ($cars as $car) {
            $this->result->rentalCars->push(
                new ItineraryRentalCar(
                    id: $car->id,
                    name: $car->name,
                    startDateTime: $car->pickupDateTime,
                    endDateTime: $car->dropoffDateTime,
                    isPlaceholder: $car->isPlaceholder
                )
            );
        }
    }

    /**
     * Push a rental car to the itinerary summary.
     *
     * @param  Collection<int, AddedUpsellItemResponseEntity>  $items
     */
    private function pushUpsellItems(Collection $items): void
    {
        foreach ($items as $item) {
            $this->result->upsellItems->push(
                new UpsellItem($item->componentRefId, $item->name)
            );
        }
    }

    /**
     * Push a transfer to the itinerary summary.
     */
    private function pushTransfer(LegConnectionEntity $connection): void
    {
        $this->result->transfers->push(
            new ItineraryTransfer(
                id: $connection->id,
                startLocationName: $connection->startLocation->name,
                endLocationName: $connection->endLocation->name,
                startDateTime: $connection->startDateTime,
                endDateTime: $connection->endDateTime,
                isPlaceholder: $connection->isPlaceholder,
                name: $connection->name
            )
        );
    }

    /**
     * Push a flight to the itinerary summary.
     */
    private function pushFlight(LegConnectionEntity $connection): void
    {
        $this->result->flights->push(
            new ItineraryFlight(
                id: $connection->id,
                startLocationName: $connection->startLocation->name,
                endLocationName: $connection->endLocation->name,
                startDateTime: $connection->startDateTime,
                endDateTime: $connection->endDateTime,
                isPlaceholder: $connection->isPlaceholder,
                name: $connection->name
            )
        );
    }

    /**
     * Push an activity to the itinerary summary.
     */
    private function pushActivity(string $id, string $name, CarbonImmutable $start, CarbonImmutable $end): void
    {
        $this->result->activities->push(
            new ItineraryActivity(
                id: $id,
                name: $name,
                startDateTime: $start,
                endDateTime: $end
            )
        );
    }
}
