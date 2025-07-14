<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Location;

use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

class CountryCodesRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of RetrieveCheckoutRequest
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/location-api-v1.html#/paths/~1countries~1calling-codes/get
     */
    public function __construct() {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/location/v1/countries/calling-codes';
    }

    /**
     * Cast the response to a DTO.
     *
     * @throws Throwable
     */
    public function createDtoFromResponse(Response $response): CountryCodesResponse
    {
        throw_unless(condition: $response->ok(), exception: NotFoundException::class);

        return CountryCodesResponse::from($response->array());
    }
}
