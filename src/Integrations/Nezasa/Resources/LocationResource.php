<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Resources;

use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountryCodesRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class LocationResource extends BaseResource
{
    /**
     * Retrieve an itinerary with the given ID.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function getCuntryCodes(): Response
    {
        return $this->connector->send(
            new CountryCodesRequest
        );
    }
}
