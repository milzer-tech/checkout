<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Passolution\Connectors;

use Illuminate\Support\Facades\Config;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Makeable;
use Saloon\Traits\Plugins\HasTimeout;

class PassolutionConnector extends Connector
{
    use HasTimeout;
    use Makeable;

    protected int $connectTimeout = 30;

    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return rtrim(Config::string('checkout.integrations.passolution.base_url'), '/');
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator(Config::string('checkout.integrations.passolution.token'));
    }
}
