<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Planner;

use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\GetItineraryResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

class GetItineraryRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of RetrieveCheckoutRequest
     */
    public function __construct(protected readonly string $itineraryId) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/planner/v1/itineraries/'.$this->itineraryId.'?include=all';
    }

    /**
     * Cast the response to a DTO.
     *
     * @throws Throwable
     */
    public function createDtoFromResponse(Response $response): GetItineraryResponse
    {
        throw_unless(condition: $response->ok(), exception: NotFoundException::class);

        return GetItineraryResponse::from($response->array(key: 'itinerary'));
    }
}
