<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Credit2000\Connectors;

use Illuminate\Support\Facades\Config;
use Milzer\HttpLogger\Core\LoggingOptions;
use Milzer\HttpLogger\Saloon\Contracts\ConfiguresLogging;
use Nezasa\Checkout\Integrations\Credit2000\Resources\Credit2000PaymentResource;
use Nezasa\Checkout\Integrations\Foundation\Traits\Connector\HasLogging;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Traits\Makeable;
use Saloon\Traits\Plugins\HasTimeout;

class Credit2000Connector extends Connector implements ConfiguresLogging
{
    use HasLogging;
    use HasTimeout;
    use Makeable;

    protected int $connectTimeout = 30;

    protected int $requestTimeout = 45;

    /**
     * Name the log entries of this connector so they are easy to find.
     */
    public function configureLogging(LoggingOptions $options, PendingRequest $pendingRequest): LoggingOptions
    {
        return $options->withOutgoingMessages(
            request: 'checkout-to-credit2000',
            response: 'credit2000-to-checkout',
            failure: 'credit2000-failed',
        );
    }

    public function resolveBaseUrl(): string
    {
        return rtrim(Config::string('checkout.integrations.credit2000.base_url'), '/');
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'text/xml, application/soap+xml, */*',
        ];
    }

    public function payment(): Credit2000PaymentResource
    {
        return new Credit2000PaymentResource($this);
    }
}
