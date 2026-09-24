<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Foundation\Traits\Connector;

use Milzer\HttpLogger\Core\HttpLogger;
use Milzer\HttpLogger\Saloon\SaloonLogging;
use Nezasa\Checkout\Support\CheckoutLogContext;
use Saloon\Http\PendingRequest;

/**
 * Logs every request of the connector and adds the checkout query parameters
 * (checkoutId, itineraryId, origin, lang, restPayment) to each log entry.
 */
trait HasLogging
{
    public function bootHasLogging(PendingRequest $pendingRequest): void
    {
        $logger = HttpLogger::resolve();

        SaloonLogging::register(
            $logger->withOptions($logger->options()->withContext(CheckoutLogContext::resolve())),
            $pendingRequest
        );
    }
}
