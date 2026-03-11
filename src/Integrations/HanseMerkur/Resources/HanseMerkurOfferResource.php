<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Resources;

use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurCreateBookingPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurCreateOffersPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurCreateBookingRequest;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurOffersRequest;
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
    public function reserveBooking(HanseMerkurCreateBookingPayload $payload): Response
    {
        return $this->connector->send(
            new HanseMerkurCreateBookingRequest($payload)
        );
    }
}
