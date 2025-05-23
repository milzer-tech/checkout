<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Resources;

use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class PlannerResource extends BaseResource
{
    /**
     * * Retrieve an itinerary with the given ID.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function getItinerary(string $itineraryId): Response
    {
        return $this->connector->send(
            new GetItineraryRequest($itineraryId)
        );
    }
}
