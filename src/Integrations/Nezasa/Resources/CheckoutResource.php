<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Resources;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddOrRemoveUpsellItemsPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\SaveTravellersDetailsPayload;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\AddOrRemoveUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\ApplyPromoCodeRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\DeletePromoCodeRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetAvailableUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\SaveTravelerDetailsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\TravelerRequirementsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\VerifyAvailabilityRequest;
use Nezasa\Checkout\Models\Checkout;
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
    public function saveTravelerDetails(string $checkoutId, SaveTravellersDetailsPayload $payload): Response
    {
        return $this->connector->send(
            new SaveTravelerDetailsRequest($checkoutId, $payload)
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

    /**
     * Apply a promo code to a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function applyPromoCode(string $checkout, string $code): Response
    {
        return $this->connector->send(
            new ApplyPromoCodeRequest($checkout, $code)
        );
    }

    /**
     * Delete a promo code from a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function deletePromoCode(string $checkoutId): Response
    {
        return $this->connector->send(
            new DeletePromoCodeRequest($checkoutId)
        );
    }

    /**
     * Retrieve available upsell items for a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function upsellItems(string $checkoutId): Response
    {
        return $this->connector->send(
            new GetAvailableUpsellItemsRequest($checkoutId)
        );
    }

    /**
     * Retrieve upsell items for a specific checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function retrieveUpsellItems(string $checkoutId): Response
    {
        return $this->connector->send(
            new RetrieveCheckoutUpsellItemsRequest($checkoutId)
        );
    }

    /**
     * Add or update upsell items for a checkout.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function addOrUpdateUpsellItem(string $checkoutId, AddOrRemoveUpsellItemsPayload $payload): Response
    {
        return $this->connector->send(
            new AddOrRemoveUpsellItemsRequest($checkoutId, $payload)
        );
    }

    /**
     * Retrieve a checkout by its model.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function varifyAvailability(string $checkoutId): Response
    {
        return $this->connector->send(
            new VerifyAvailabilityRequest($checkoutId)
        );
    }
}
