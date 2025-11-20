<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\VerifyAvailabilityAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Models\Checkout;

class TripSummary extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.trip-summary');
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $prices
     */
    #[On('price-changed')]
    public function priceChanged(array $prices): void
    {
        $prices = ApplyPromoCodeResponse::from($prices);

        $this->itinerary->price = $prices->discountedPackagePrice ?? $prices->packagePrice;

        $this->itinerary->promoCodeResponse = $prices;
    }

    /**
     * Handle the summary updated event.
     */
    #[On('summary-updated')]
    public function summaryUpdated(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $summary = Checkout::whereCheckoutId($this->itineraryId)->value('data')->get('status')['summary'];

            if ($summary['isCompleted']) {
                break;
            }

            sleep(1);
        }

        $prices = NezasaConnector::make()->checkout()->retrieve($this->itineraryId)->dto()->prices;

        $this->itinerary->price = $prices->discountedPackagePrice ?? $prices->packagePrice;
    }

    /**
     * Handle the promo code applied event.
     */
    #[On('payment-selected')]
    public function verifyAvailability(): void
    {
        $this->dispatch(
            event: 'availability-verified',
            result: resolve(VerifyAvailabilityAction::class)->run($this->checkoutId, $this->itinerary),
        );
    }
}
