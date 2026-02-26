<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Connectors;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\HanseMerkur\Resources\HanseMerkurOfferResource;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Makeable;

/**
 * Hanse merkur Connector
 *
 * This class is responsible for connecting to Hanse merkur API.
 * It sets the base URL, default headers, and authentication method.
 *
 * @link https://api-fbt.hmrv.de/rest/swagger-ui/index.html#/
 */
class HanseMerkurConnector extends Connector
{
    use Makeable;

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return Config::string('checkout.insurance.hanse_merkur.base_url');
    }

    /**
     * Default Request Headers
     *
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-Key' => Config::string('checkout.insurance.hanse_merkur.api_key'),
        ];
    }

    /**
     * Default authenticator used.
     */
    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(
            Config::string('checkout.insurance.hanse_merkur.username'),
            Config::string('checkout.insurance.hanse_merkur.password')
        );
    }

    public function offers(): HanseMerkurOfferResource
    {
        return new HanseMerkurOfferResource($this);
    }
}
