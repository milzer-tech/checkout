<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Nezasa\Checkout\Events\ItineraryBookingFailedEvent;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Mails\CubaTravelMail;
use Nezasa\Checkout\Models\Checkout;

final class SendEmailToCubaTravelListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ItineraryBookingFailedEvent|ItineraryBookingSucceededEvent $event): void
    {
        $receivers = Config::array('checkout::cuba-travel.email.to');

        if ($receivers === []) {
            return;
        }

        $checkout = Checkout::whereCheckoutId($event->checkoutId)->whereItineraryId($event->itineraryId)->firstOrFail();

        Mail::to($receivers)->send(mailable: new CubaTravelMail($checkout));
    }
}
