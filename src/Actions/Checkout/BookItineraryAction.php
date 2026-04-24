<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Saloon\Http\Response;
use Throwable;

class BookItineraryAction
{
    /**
     * Handle the booking of the itinerary.
     */
    public function run(string $checkoutId): ?Response
    {
        try {
            return NezasaConnector::make()->checkout()->synchronousBooking($checkoutId);
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

    }
}
