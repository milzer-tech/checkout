<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Throwable;

class TripSummary extends Component
{
    /**
     * The unique identifier for the itinerary
     */
    #[Url]
    public string $itineraryId;

    /**
     * The unique identifier for the checkout session.
     */
    #[Url]
    public string $checkoutId;

    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerarySummary;

    /**
     * Indicates whether the trip details in the view are expanded.
     */
    public bool $isExpanded = true;

    /**
     * Mount the component to initialize its actions and properties.
     *
     * @throws Throwable
     */
    public function mount(SummarizeItineraryAction $summerizeItinerary): void
    {
        $this->itinerarySummary = $summerizeItinerary->handle($this->itineraryId, $this->checkoutId);
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.trip-summary')->with('itinerary', $this->itinerarySummary);
    }
}
