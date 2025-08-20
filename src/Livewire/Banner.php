<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;

class Banner extends Component
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    public function render(): View
    {
        return view('checkout::trip-details-page.banner');
    }
}
