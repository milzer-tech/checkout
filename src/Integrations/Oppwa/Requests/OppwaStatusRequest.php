<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class OppwaStatusRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of ApplyPromoCodeRequest
     */
    public function __construct(public string $resourcePath) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return $this->resourcePath;
    }
}
