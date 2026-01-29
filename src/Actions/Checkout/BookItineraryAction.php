<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Payments\Enums\BookingStatusEnum;
use Throwable;

class BookItineraryAction
{
    /**
     * Handle the booking of the itinerary.
     */
    public function run(string $checkoutId): BookingStatusEnum
    {
        try {
            $failures = collect();
            $successes = collect();

            NezasaConnector::make()->checkout()->synchronousBooking($checkoutId)
                ->collect('summary.components')
                ->reject(fn (array $item) => $item['isPlaceholder'])
                ->each(
                    fn (array $item) => $item['isBooked'] ? $successes->add($item['id']) : $failures->add($item['id'])
                );

            if ($successes->isNotEmpty() && $failures->isEmpty()) {
                return BookingStatusEnum::CompleteSuccess;
            }

            if ($successes->isEmpty() && $failures->isNotEmpty()) {
                return BookingStatusEnum::CompleteFailed;
            }

            if ($successes->isNotEmpty() && $failures->isNotEmpty()) {
                return BookingStatusEnum::PartialFailure;
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return BookingStatusEnum::Unknown;
    }
}
