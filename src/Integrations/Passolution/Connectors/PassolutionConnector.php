<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Passolution\Connectors;

use Illuminate\Support\Facades\Config;
use Milzer\HttpLogger\Core\LoggingOptions;
use Milzer\HttpLogger\Saloon\Contracts\ConfiguresLogging;
use Nezasa\Checkout\Integrations\Foundation\Traits\Connector\HasLogging;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Traits\Makeable;
use Saloon\Traits\Plugins\HasTimeout;

class PassolutionConnector extends Connector implements ConfiguresLogging
{
    use HasLogging;
    use HasTimeout;
    use Makeable;

    protected int $connectTimeout = 30;

    protected int $requestTimeout = 30;

    /**
     * Name the log entries of this connector so they are easy to find.
     */
    public function configureLogging(LoggingOptions $options, PendingRequest $pendingRequest): LoggingOptions
    {
        return $options->withOutgoingMessages(
            request: 'checkout-to-passolution',
            response: 'passolution-to-checkout',
            failure: 'passolution-failed',
        );
    }

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
