<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Livewire\Component;

class TravelerDetails extends Component
{
    /**
     * The unique identifier for the checkout process.
     */
    public string $checkoutId;

    /**
     * The unique identifier for the itinerary
     */
    public string $itineraryId;

    /**
     * Indicates the request's source from the IBE or the APP.
     * This can help determine if the user is authenticated (APP) or not (IBE).
     */
    public string $origin;

    /**
     * The ISO 639-1 language code representing the user's language preference for the itinerary.
     */
    public string $lang;

    /**
     * Mount the component with the request data.
     */
    public function mount(Request $request): void
    {
        $this->checkoutId = $request->query('checkoutId');
        $this->itineraryId = $request->query('itineraryId');
        $this->origin = $request->query('origin');
        $this->lang = $request->query('lang');
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::traveler-details');
    }
}
