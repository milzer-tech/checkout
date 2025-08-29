<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Models\Checkout;

class ConfirmationPage extends BaseCheckoutComponent
{
    /**
     * Holds the names of the travelers.
     *
     * @var array<int, string>
     */
    public array $travelers = [];

    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * Mount the component and initialize requirements.
     */
    public function mount(): void
    {
        $this->initializeRequirements();

        foreach ($this->model->data['paxInfo'] as $room) {
            foreach ($room as $pax) {
                $this->travelers[] = $pax['firstName'].' '.$pax['lastName'];
            }
        }

    }

    /**
     * Render the view for the confirmation page.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.confirmation-page');
    }

    /**
     * Initialize the requirements for the payment page.
     */
    protected function initializeRequirements(): void
    {
        $this->model = Checkout::with('lastestTransaction')->whereCheckoutId($this->checkoutId)->firstOrFail();

        $result = resolve(CallTripDetailsAction::class)->run($this->itineraryId, $this->checkoutId);

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            $result->itinerary,
            $result->checkout,
            $result->addedRentalCars,
            collect($result->addedUpsellItems),
        );

        $this->itinerary->price = $this->model->lastestTransaction->price;
    }
}
