<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Listeners;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Insurances\Handlers\InsuranceHandler;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;

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

            $checkoutData = InsuranceCheckoutData::checkoutDataArray($event->transaction->checkout->data);
            if (InsuranceCheckoutData::hasSelectedOffer($checkoutData)) {
                $this->insuranceHandler->bookOffer($event->transaction);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
