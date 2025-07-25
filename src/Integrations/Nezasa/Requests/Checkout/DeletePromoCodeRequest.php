<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeletePromoCodeRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::DELETE;

    /**
     * Create a new instance of ApplyPromoCodeRequest
     */
    public function __construct(
        public readonly string $checkoutId,
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/promo-codes";
    }
}
