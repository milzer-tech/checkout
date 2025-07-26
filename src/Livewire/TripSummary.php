<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;

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
        return view('checkout::trip-details-page.trip-summary')->with('itinerary', $this->itinerary);
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $applyPromoCodeResponse
     */
    #[On('promoCode-applied')]
    public function promoCodeApplied(array $applyPromoCodeResponse): void
    {
        $promoCodeResponse = ApplyPromoCodeResponse::from($applyPromoCodeResponse);

        $this->itinerary->price = $promoCodeResponse->discountedPackagePrice;

        $this->itinerary->promoCodeResponse = $promoCodeResponse;
    }
}
