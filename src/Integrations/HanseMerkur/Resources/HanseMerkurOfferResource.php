<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Resources;

use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurCreateOffersPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurOffersRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class HanseMerkurOfferResource extends BaseResource
{
    /**
     * Retrieve an itinerary with the given ID.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function create(HanseMerkurCreateOffersPayload $payload): Response
    {
        return $this->connector->send(
            new HanseMerkurOffersRequest($payload)
        );
    }
}
