<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Resources;

use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurCreateOffersPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurReservePayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurOffersRequest;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurReserveRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class HanseMerkurOfferResource extends BaseResource
{
    /**
     * Create the insurance offers.
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

    /**
     * Reserve a booking of an insurance offer.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function reserve(HanseMerkurReservePayload $payload): Response
    {
        return $this->connector->send(
            new HanseMerkurReserveRequest($payload)
        );
    }
}
