<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Nezasa\Checkout\Events\ItineraryBookingFailedEvent;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Listeners\SendEmailToCubaTravelListener;

class CheckoutEventServiceProvider extends EventServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ItineraryBookingSucceededEvent::class, SendEmailToCubaTravelListener::class);

        Event::listen(ItineraryBookingFailedEvent::class, SendEmailToCubaTravelListener::class);
    }
}
