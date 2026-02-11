<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Saloon\Http\Response;

class BookItineraryAction
{
    /**
     * Handle the booking of the itinerary.
     */
    public function run(string $checkoutId): Response
    {
        return NezasaConnector::make()->checkout()->synchronousBooking($checkoutId);
    }
}
