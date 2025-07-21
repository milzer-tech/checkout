<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Resources;

use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\SaveTravelerDetailsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\TravelerRequirementsRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class CheckoutResource extends BaseResource
{
    /**
     * * Retrieve a checkout by its ID.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function retrieve(string $checkoutId): Response
    {
        return $this->connector->send(
            new RetrieveCheckoutRequest($checkoutId)
        );
    }

    /**
     * Save traveler details for a specific checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function saveTravelerDetails(string $checkoutId): Response
    {
        return $this->connector->send(
            new SaveTravelerDetailsRequest($checkoutId)
        );
    }

    /**
     * Retrieve travel requirements
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function travelerRequirements(string $checkoutId): Response
    {
        return $this->connector->send(
            new TravelerRequirementsRequest($checkoutId)
        );
    }
}
