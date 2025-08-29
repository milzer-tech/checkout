<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;

class Banner extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * Render the view for the banner.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line  */
        return view('checkout::blades.banner');
    }
}
