<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Enums\Section;

class ActivitySection extends BaseCheckoutComponent
{
    //    /**
    //     * The summary of the itinerary.
    //     */
    //    public ItinerarySummary $itinerary;

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::blades.activity-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Activity);
    }
}
