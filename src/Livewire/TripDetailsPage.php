<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class TripDetailsPage extends Component
{
    /**
     * The unique identifier for the checkout process.
     */
    #[Url]
    public string $checkoutId;

    /**
     * The unique identifier for the itinerary
     */
    #[Url]
    public string $itineraryId;

    /**
     * Indicates the request's source from the IBE or the APP.
     * This can help determine if the user is authenticated (APP) or not (IBE).
     */
    #[Url]
    public string $origin;

    /**
     * The ISO 639-1 language code representing the user's language preference for the itinerary.
     */
    #[Url]
    public string $lang;

    public $totalPrice;

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.index');
    }
}
