<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\TripDetails;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Response;
use Throwable;

class CallTripDetailsAction
{
    /**
     * Execute the action to retrieve itinerary and checkout details.
     *
     * @return Collection {
     *                    'itinerary': GetItineraryResponse,
     *                    'checkout': RetrieveCheckoutResponse
     *                    }
     *
     * @throws Throwable
     */
    public function run(string $itineraryId, string $checkoutId): Collection
    {
        $results = new Collection;

        NezasaConnector::make()
            ->pool([
                'itinerary' => new GetItineraryRequest($itineraryId),
                'checkout' => new RetrieveCheckoutRequest($checkoutId),
            ])
            ->withResponseHandler(fn (Response $response, string $key) => $results->put($key, $response->dto()))
            ->send()
            ->wait();

        return $results;
    }
}
