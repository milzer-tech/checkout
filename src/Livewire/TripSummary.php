<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
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
    public function mount(ItinerarySummary $itinerarySummary): void
    {
        $this->itinerarySummary = $itinerarySummary;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.trip-summary')->with('itinerary', $this->itinerarySummary);
    }
}
