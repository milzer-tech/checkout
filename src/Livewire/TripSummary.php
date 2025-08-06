<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Models\Checkout;

class TripSummary extends Component
{
    /**
     * The unique identifier for the itinerary
     */
    #[Url]
    public string $itineraryId;

    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * Indicates whether the trip details in the view are expanded.
     */
    public bool $isExpanded = true;

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.trip-summary');
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $prices
     */
    #[On('price-changed')]
    public function priceChanged(array $prices): void
    {
        $promoCodeResponse = ApplyPromoCodeResponse::from($prices);

        $this->itinerary->price = $promoCodeResponse->discountedPackagePrice;

        $this->itinerary->promoCodeResponse = $promoCodeResponse;
    }

    /**
     * Handle the summary updated event.
     */
    #[On('summary-updated')]
    public function summaryUpdated(): void
    {
        do {
            sleep(1);

            $summary = Checkout::whereCheckoutId($this->itineraryId)->value('data')->get('status')['summary'];
        } while (! $summary['isCompleted']);

        $prices = NezasaConnector::make()->checkout()->retrieve($this->itineraryId)->dto()->prices;

        $this->itinerary->price = $prices->discountedPackagePrice ?? $prices->packagePrice;
    }
}
