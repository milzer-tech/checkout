<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Vertical\Connectors;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Vertical\Resources\VerticalPurchaseResource;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Makeable;
use Saloon\Traits\Plugins\HasTimeout;

/**
 * Vertical Insurance Connector
 *
 * This class is responsible for connecting to the Computop API.
 * It sets the base URL, default headers, and authentication method.
 *
 * @see https://docs.verticalinsure.com/api
 */
class VerticalInsuranceConnector extends Connector
{
    use HasTimeout;
    use Makeable;

    /**
     * The timeout in seconds for the connection according to the Nezasa API.
     */
    protected int $connectTimeout = 60;

    /**
     * The timeout in seconds for the request according to the Nezasa API.
     */
    protected int $requestTimeout = 60;

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.verticalinsure.com/v1';
    }

    /**
     * Default Request Headers
     *
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Default authenticator used.
     */
    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(
            Config::string('checkout.insurance.vertical.username'),
            Config::string('checkout.insurance.vertical.password')
        );
    }

    /**
     * Get the payment resource.
     */
    public function purchase(): VerticalPurchaseResource
    {
        return new VerticalPurchaseResource($this);
    }
}
