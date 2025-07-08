<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Planner;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SaveTravelerDetailsRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of RetrieveCheckoutRequest
     */
    public function __construct(protected readonly string $checkoutId) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/traveler-details";
    }
}
