<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Connectors;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\HanseMerkur\Resources\HanseMerkurOfferResource;
use Saloon\Contracts\Body\HasBody;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Makeable;

/**
 * Hanse merkur Connector
 *
 * This class is responsible for connecting to Hanse merkur API.
 * It sets the base URL, default headers, and authentication method.
 *
 * @link https://api-fbt.hmrv.de/rest/swagger-ui/index.html#/
 */
class HanseMerkurConnector extends Connector implements HasBody
{
    use HasJsonBody;
    use Makeable;

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return Config::string('checkout.insurance.hanse_merkur.offers_base_url');
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

    /**
     * Add a default body to all requests
     *
     * @return array<string, array<string, string|null>>
     */
    protected function defaultBody(): array
    {
        return [
            'metadata' => [
                'requestorId' => Config::string('checkout.insurance.hanse_merkur.requester_id'),
                'partnerId' => Config::string('checkout.insurance.hanse_merkur.partner_id'),
                'externalPartnerId' => null,
            ],
        ];
    }

    public function offers(): HanseMerkurOfferResource
    {
        return new HanseMerkurOfferResource($this);
    }
}
