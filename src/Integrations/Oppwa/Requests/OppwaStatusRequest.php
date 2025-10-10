<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Requests;

use Illuminate\Support\Facades\Config;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

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

    public function hasRequestFailed(Response $response): ?bool
    {
        $successfulResultCode = Config::string('checkout.integrations.oppwa.successful_result_code');

        try {
            return $response->array('result.code') !== $successfulResultCode;
        } catch (Throwable $exception) {
            report($exception);

            return true;
        }
    }
}
