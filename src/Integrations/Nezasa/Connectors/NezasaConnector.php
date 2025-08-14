<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Connectors;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Nezasa\Resources\CheckoutResource;
use Nezasa\Checkout\Integrations\Nezasa\Resources\LocationResource;
use Nezasa\Checkout\Integrations\Nezasa\Resources\PaymentTransactionResource;
use Nezasa\Checkout\Integrations\Nezasa\Resources\PlannerResource;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Makeable;
use Saloon\Traits\Plugins\HasTimeout;

/**
 * Nezasa Connector
 *
 * This class is responsible for connecting to the Nezasa API.
 * It sets the base URL, default headers, and authentication method.
 *
 * @link https://support.nezasa.com/hc/en-gb/articles/29588280597265-Checkout-API
 */
class NezasaConnector extends Connector
{
    use HasTimeout;
    use Makeable;

    /**
     * The timeout in seconds for the connection according to the Nezasa API.
     */
    protected int $connectTimeout = 30;

    /**
     * The timeout in seconds for the request according to the Nezasa API.
     */
    protected int $requestTimeout = 30;

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return Config::string('checkout.nezasa.base_url');
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
        ];
    }

    /**
     * Default authenticator used.
     */
    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(
            Config::string('checkout.nezasa.username'),
            Config::string('checkout.nezasa.password')
        );
    }

    /**
     * Get the checkout resource.
     */
    public function checkout(): CheckoutResource
    {
        return new CheckoutResource($this);
    }

    /**
     * Get the itinerary resource.
     */
    public function planner(): PlannerResource
    {
        return new PlannerResource($this);
    }

    /**
     * Get the location resource.
     */
    public function location(): LocationResource
    {
        return new LocationResource($this);
    }

    /**
     * Get the payment transaction resource.
     */
    public function paymentTransaction(): PaymentTransactionResource
    {
        return new PaymentTransactionResource($this);
    }
}
