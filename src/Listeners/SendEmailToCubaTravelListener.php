<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Nezasa\Checkout\Events\ItineraryBookingFailedEvent;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Mails\CubaTravelMail;

final class SendEmailToCubaTravelListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ItineraryBookingFailedEvent|ItineraryBookingSucceededEvent $event): void
    {
        if (! Config::boolean('checkout::cuba-travel.active')) {
            return;
        }

        $receivers = Config::array('checkout::cuba-travel.email.to');

        if ($receivers === []) {
            return;
        }

        Mail::to($receivers)->send(
            mailable: new CubaTravelMail($event->transaction->checkout)
        );
    }
}
