<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ItineraryBookingSucceededEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new instance of the event.
     */
    public function __construct(public string $checkoutId, public string $itineraryId) {}
}
