<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\TripDetails;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\Planner\RequiredResponses;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetAvailableUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\TravelerRequirementsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountriesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountryCodesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\AddedRentalCarsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Response;
use Throwable;

class CallTripDetailsAction
{
    /**
     * Execute the action to retrieve itinerary and checkout details.
     *
     * @throws Throwable
     */
    public function run(string $itineraryId, string $checkoutId): RequiredResponses
    {
        $results = new Collection;
        $requests = [
            'itinerary' => new GetItineraryRequest($itineraryId),
            'checkout' => new RetrieveCheckoutRequest($checkoutId),
            'addedRentalCars' => new AddedRentalCarsRequest($itineraryId),
            'travelerRequirements' => new TravelerRequirementsRequest($checkoutId),
            'upsellItems' => new GetAvailableUpsellItemsRequest($checkoutId),
            'addedUpsellItems' => new RetrieveCheckoutUpsellItemsRequest($checkoutId),
            'countryCodes' => new CountryCodesRequest,
            'countries' => new CountriesRequest,
        ];

        NezasaConnector::make()
            ->pool(requests: $requests, concurrency: count($requests))
            ->withExceptionHandler(fn ($exception) => throw new NotFoundException)
            ->withResponseHandler(function (Response $response, string $key) use ($results) {
                $results->put($key, $response->dto());
            })
            ->send()
            ->wait();

        return RequiredResponses::from($results);
    }
}
