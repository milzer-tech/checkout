<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Payments\Enums\BookingStatusEnum;

class FindBookingResultAction
{
    /**
     * Determine booking status based on summary of bookings.
     *
     * @param  array<string, array<string, mixed>>  $summary
     */
    public function run(array $summary): BookingStatusEnum
    {
        $failures = collect();
        $successes = collect();

        try {
            collect($summary['components'])
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
        } catch (\Throwable $e) {
            report($e);
        }

        return BookingStatusEnum::Unknown;
    }
}
