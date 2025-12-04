<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Nezasa\Checkout\Events\ItineraryBooked;
use Nezasa\Checkout\Listeners\SendEmailToCubaTravelListener;

class CheckoutEventServiceProvider extends EventServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ItineraryBooked::class, SendEmailToCubaTravelListener::class);
    }
}
