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
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerarySummary;

    /**
     * Indicates whether the trip details in the view are expanded.
     */
    public bool $isExpanded = false;

    public $tripDetails = [
        'title' => 'Palma de Mallorca',
    ];

    public $totalPrice = '1,234.56';

    /**
     * Mount the component to initialize its actions and properties.
     *
     * @throws Throwable
     */
    public function mount(SummarizeItineraryAction $summerizeItinerary): void
    {
        $this->itinerarySummary = $summerizeItinerary->handle($this->itineraryId);
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.trip-summary')->with('itinerary', $this->itinerarySummary);
    }

    /**
     * Toggle the expansion state of the trip details.
     */
    public function toggleExpand(): void
    {
        $this->isExpanded = ! $this->isExpanded;
    }
}
