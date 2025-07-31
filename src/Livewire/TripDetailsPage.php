<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\InitializeCheckoutDataAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Throwable;

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

    /**
     * Render the component view.
     *
     * @throws Throwable
     */
    public function render(
        CallTripDetailsAction $callTripDetails,
        SummarizeItineraryAction $summerizeItinerary,
        InitializeCheckoutDataAction $initializeCheckoutData
    ): View {
        $result = $callTripDetails->run($this->itineraryId, $this->checkoutId);

        $model = $initializeCheckoutData->run(checkoutId: $this->checkoutId, allocatedPax: $result['itinerary']->allocatedPax);

        return view('checkout::trip-details-page.index', [
            'itinerary' => $summerizeItinerary->run($result['itinerary'], $result['checkout']),
            'contactRequirements' => $result['travelerRequirements']->contact,
            'countryCodes' => $result['countryCodes'],
            'allocatedPax' => $result['itinerary']->allocatedPax,
            'passengerRequirements' => $result['travelerRequirements']->passenger,
            'countriesResponse' => $result['countries'],
            'prices' => $result['checkout']->prices,
            'model' => $model,
            'upsellItems' => $result['upsellItems'],
        ]);
    }
}
