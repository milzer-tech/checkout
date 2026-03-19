<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Listeners;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Insurances\Handlers\InsuranceHandler;

final readonly class InsuranceListener
{
    /**
     * Create a new instance of InsuranceListener
     */
    public function __construct(private InsuranceHandler $insuranceHandler) {}

    /**
     * Handle the event.
     */
    public function handle(ItineraryBookingSucceededEvent $event): void
    {
        try {
            if (Config::boolean('checkout.insurance.vertical.active')) {
                return;
            }

            if (data_get(target: $event->transaction->checkout->data, key: 'insurance.id', default: false)) {
                $this->insuranceHandler->bookOffer($event->transaction);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
