<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\VerifyAvailabilityAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;

class TripSummary extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * Whether to show the price breakdown.
     */
    public bool $showPriceBreakdown = false;

    public string $nezasaPlannerUrl;

    public function mount(): void
    {
        $this->nezasaPlannerUrl = config('checkout.nezasa.base_url').'/itineraries/'.$this->itineraryId;

        $this->showPriceBreakdown = $this->itinerary->price->externallyPaidCharges->externallyPaidCharges->isNotEmpty();
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.trip-summary');
    }

    /**
     * Collapse or expand the price breakdown.
     */
    public function togglePriceBreakdown(): void
    {
        $this->showPriceBreakdown = ! $this->showPriceBreakdown;
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $prices
     */
    #[On('price-changed')]
    public function priceChanged(array $prices): void
    {
        $this->itinerary->price = ApplyPromoCodeResponse::from($prices);
    }

    /**
     * Handle the summary updated event.
     */
    #[On('summary-updated')]
    public function summaryUpdated(): void
    {
        $result = resolve(CallTripDetailsAction::class)->run($this->itineraryId, $this->checkoutId);

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result->itinerary,
            checkoutResponse: $result->checkout,
            addedRentalCarResponse: $result->addedRentalCars,
            addedUpsellItemsResponse: collect($result->addedUpsellItems),
        );
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
