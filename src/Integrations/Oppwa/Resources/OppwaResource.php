<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Resources;

use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaPrepareRequest;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaStatusRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class OppwaResource extends BaseResource
{
    /**
     * Retrieve an itinerary with the given ID.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function prepare(OppwaPreparePayload $payload): Response
    {
        return $this->connector->send(
            new OppwaPrepareRequest($payload)
        );
    }

    /**
     * Get the status of a resource by its path.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function status(string $resourcePath): Response
    {
        return $this->connector->send(
            new OppwaStatusRequest($resourcePath)
        );
    }
}
