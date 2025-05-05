<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Connectors;

use Saloon\Http\Connector;

final class NezasaConnector extends Connector
{
    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string {}
}
