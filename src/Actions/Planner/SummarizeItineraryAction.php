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
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\LegConnectionEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\LegResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\GetItineraryResponse;
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
     * @throws Throwable
     */
    public function handle(string $itineraryId): ItinerarySummary
    {
        $itineraryResponse = $this->retrieveItinerary($itineraryId);

        $this->initializeResult($itineraryResponse);
        $this->pushTransport($itineraryResponse->startConnections);
        $this->pushTransport($itineraryResponse->returnConnections);

        foreach ($itineraryResponse->modules as $module) {
            $this->pushTransport($module->returnConnections);
            foreach ($module->legs as $leg) {
                $this->pushAccommodation($leg);
                $this->pushActivities($leg);
                $this->pushTransport($leg->connections);
            }
        }

        return $this->result;
    }

    /**
     * Initialize the result with the start and end dates from the itinerary.
     */
    private function initializeResult(GetItineraryResponse $itineraryResponse): void
    {
        $this->result = new ItinerarySummary(
            price: $itineraryResponse->priceInfo->packagePrice,
            title: str($itineraryResponse->title),
            startDate: $itineraryResponse->startDate,
            endDate: $itineraryResponse->endDate,
            adults: $itineraryResponse->countAdults(),
            children: $itineraryResponse->countChildren(),
            childrenAges: $itineraryResponse->getChildrenAges()
        );
    }

    /**
     * Retrieve the itinerary by its ID.
     *
     * @throws Throwable
     */
    private function retrieveItinerary(string $itineraryId): GetItineraryResponse
    {
        //        dd(
        //            json_encode(
        //                NezasaConnector::make()
        //                    ->planner()
        //                    ->getItinerary($itineraryId)
        //                ->array()
        //            )
        //        );
        return NezasaConnector::make()
            ->planner()
            ->getItinerary($itineraryId)
            ->dto();
    }

    /**
     * Extract accommodations from a leg and add them to the itinerary summary.
     */
    private function pushAccommodation(LegResponseEntity $leg): void
    {
        foreach ($leg->stop->accommodations as $hotel) {
            $this->result->stays->push(
                new ItineraryStay(
                    name: str($hotel->location->name),
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
            $this->pushActivity($activity->name, $activity->startDateTime, $activity->endDateTime);
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
            match ($connection->connectionType) {
                'RentalCar' => $this->pushRentalCar($connection),
                'Transfer' => $this->pushTransfer($connection),
                'Flight' => $this->pushFlight($connection),
                'Activity' => $this->pushActivity(
                    $connection->name,
                    $connection->startDateTime,
                    $connection->endDateTime
                ),
            };
        }
    }

    /**
     * Push a rental car to the itinerary summary.
     */
    private function pushRentalCar(LegConnectionEntity $connection): void
    {
        $this->result->rentalCars->push(
            new ItineraryRentalCar(
                name: $connection->name,
                startDateTime: $connection->startDateTime,
                endDateTime: $connection->endDateTime
            )
        );
    }

    /**
     * Push a transfer to the itinerary summary.
     */
    private function pushTransfer(LegConnectionEntity $connection): void
    {
        $this->result->transfers->push(
            new ItineraryTransfer(
                startLocationName: $connection->startLocation->name,
                endLocationName: $connection->endLocation->name,
                startDateTime: $connection->startDateTime,
                endDateTime: $connection->endDateTime,
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
                startLocationName: $connection->startLocation->name,
                endLocationName: $connection->endLocation->name,
                startDateTime: $connection->startDateTime,
                endDateTime: $connection->endDateTime,
                name: $connection->name
            )
        );
    }

    /**
     * Push an activity to the itinerary summary.
     */
    private function pushActivity(string $name, CarbonImmutable $start, CarbonImmutable $end): void
    {
        $this->result->activities->push(
            new ItineraryActivity(
                name: str($name),
                startDateTime: $start,
                endDateTime: $end
            )
        );
    }
}
