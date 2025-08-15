<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SynchronousBookingRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * The method for the request.
     */
    public function __construct(public readonly string $checkoutId) {}

    public function resolveEndpoint(): string
    {
        return "checkout/v1/checkouts/$this->checkoutId/book";
    }
}
