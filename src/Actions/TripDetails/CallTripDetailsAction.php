<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\TripDetails;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetAvailableUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\TravelerRequirementsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountriesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountryCodesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Response;
use Throwable;

class CallTripDetailsAction
{
    /**
     * Create a new instance of the CallTripDetailsAction.
     */
    public function __construct(private readonly NezasaConnector $nezasaConnector) {}

    /**
     * Execute the action to retrieve itinerary and checkout details.
     *
     * @return Collection {
     *                    'itinerary': GetItineraryResponse,
     *                    'checkout': RetrieveCheckoutResponse,
     *                    'travelerRequirements': TravelerRequirementsResponse,
     *                    'countryCodes': CountryCodesResponse,
     *                    `                  'countries': CountriesResponse
     *                    }
     *
     * @throws Throwable
     */
    public function run(string $itineraryId, string $checkoutId): Collection
    {
        $results = new Collection;
        $requests = [
            'itinerary' => new GetItineraryRequest($itineraryId),
            'checkout' => new RetrieveCheckoutRequest($checkoutId),
            'travelerRequirements' => new TravelerRequirementsRequest($checkoutId),
            'upsellItems' => new GetAvailableUpsellItemsRequest($checkoutId),
            'addedUpsellItems' => new RetrieveCheckoutUpsellItemsRequest($checkoutId),
            'countryCodes' => new CountryCodesRequest,
            'countries' => new CountriesRequest,
        ];

        $this->nezasaConnector
            ->pool(requests: $requests, concurrency: count($requests))
            ->withExceptionHandler(fn ($exception) => throw new NotFoundException)
            ->withResponseHandler(fn (Response $response, string $key) => $results->put($key, $response->dto()))
            ->send()
            ->wait();

        return $results;
    }
}
