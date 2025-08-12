<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Connectors;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Oppwa\Resources\OppwaResource;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Makeable;
use Saloon\Traits\Plugins\HasTimeout;

/**
 * Nezasa Connector
 *
 * This class is responsible for connecting to the Nezasa API.
 * It sets the base URL, default headers, and authentication method.
 *
 * @see https://axcessms.docs.oppwa.com/integrations/widget
 */
class OppwaConnector extends Connector
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
        return Config::string('checkout.payment.oppwa.base_url');
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
        ];
    }

    /**
     * Default query parameters for the requests.
     *
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'entityId' => Config::string('checkout.payment.oppwa.entity_id'),
        ];
    }

    /**
     * Default authenticator used.
     */
    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator(Config::string('checkout.payment.oppwa.token'));
    }

    /**
     * Get the checkout resource.
     */
    public function checkout(): OppwaResource
    {
        return new OppwaResource($this);
    }
}
