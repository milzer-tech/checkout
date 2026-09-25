<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Connectors;

use Illuminate\Support\Facades\Config;
use Milzer\HttpLogger\Core\LoggingOptions;
use Milzer\HttpLogger\Saloon\Contracts\ConfiguresLogging;
use Nezasa\Checkout\Integrations\Computop\Resources\ComputopPaymentResource;
use Nezasa\Checkout\Integrations\Foundation\Traits\Connector\HasLogging;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Traits\Makeable;
use Saloon\Traits\Plugins\HasTimeout;

/**
 * Computop Connector
 *
 * This class is responsible for connecting to the Computop API.
 * It sets the base URL, default headers, and authentication method.
 *
 * @see https://app.swaggerhub.com/apis-docs/Computop/Paygate_REST_API/1#/Payments
 */
class ComputopConnector extends Connector implements ConfiguresLogging
{
    use HasLogging;
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
     * Name the log entries of this connector so they are easy to find.
     */
    public function configureLogging(LoggingOptions $options, PendingRequest $pendingRequest): LoggingOptions
    {
        return $options->withOutgoingMessages(
            request: 'checkout-to-computop',
            response: 'computop-to-checkout',
            failure: 'computop-failed',
        );
    }

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return Config::string('checkout.integrations.computop.base_url');
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
            Config::string('checkout.integrations.computop.username'),
            Config::string('checkout.integrations.computop.password')
        );
    }

    /**
     * Get the payment resource.
     */
    public function payment(): ComputopPaymentResource
    {
        return new ComputopPaymentResource($this);
    }
}
