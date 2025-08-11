<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\InitializeCheckoutDataAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Throwable;

class TripDetailsPage extends BaseCheckoutComponent
{
    /**
     * The itinerary summary of the trip details page.
     */
    public ItinerarySummary $itinerary;

    /**
     * The object containing the checkout data.
     */
    public Collection $result;

    public function mount(
        CallTripDetailsAction $callTripDetails,
        SummarizeItineraryAction $summerizeItinerary,
        InitializeCheckoutDataAction $initializeCheckoutData
    ): void {
        $this->result = $callTripDetails->run($this->itineraryId, $this->checkoutId);

        $this->model = $initializeCheckoutData->run(
            checkoutId: $this->checkoutId,
            allocatedPax: $this->result['itinerary']->allocatedPax
        );

        $this->itinerary = $summerizeItinerary->run(
            itineraryResponse: $this->result['itinerary'],
            checkoutResponse: $this->result['checkout'],
            addedRentalCarResponse: $this->result['addedRentalCars'],
            addedUpsellItemsResponse: collect($this->result['addedUpsellItems']),
        );
    }

    /**
     * Render the component view.
     *
     * @throws Throwable
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.index', [
            'contactRequirements' => $this->result['travelerRequirements']->contact,
            'countryCodes' => $this->result['countryCodes'],
            'allocatedPax' => $this->result['itinerary']->allocatedPax,
            'passengerRequirements' => $this->result['travelerRequirements']->passenger,
            'countriesResponse' => $this->result['countries'],
            'prices' => $this->result['checkout']->prices,
            'upsellItemsResponse' => $this->result['upsellItems'],
            'addedUpsellItems' => $this->result['addedUpsellItems'],
        ]);
    }
}
