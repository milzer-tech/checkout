<?php

namespace Nezasa\Checkout\Livewire;

use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Models\Checkout;

class ConfirmationPage extends BaseCheckoutComponent
{
    /**
     * Holds the names of the travelers.
     *
     * @var array<int, string>
     */
    public array $travelers = [];

    public $itinerary;

    public function mount()
    {
        $this->initializeRequirements();

        foreach ($this->model->data['paxInfo'] as $room) {
            foreach ($room as $pax) {
                $this->travelers[] = $pax['firstName'].' '.$pax['lastName'];
            }
        }

    }

    public function render()
    {
        return view('checkout::trip-details-page.confirmation-page');
    }

    /**
     * Initialize the requirements for the payment page.
     */
    protected function initializeRequirements(): void
    {
        $this->model = Checkout::with('lastestTransaction')->whereCheckoutId($this->checkoutId)->firstOrFail();

        $result = resolve(CallTripDetailsAction::class)->run($this->itineraryId, $this->checkoutId);

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result['itinerary'],
            checkoutResponse: $result['checkout'],
            addedRentalCarResponse: $result['addedRentalCars'],
            addedUpsellItemsResponse: collect($result['addedUpsellItems']),
        );

        $this->itinerary->price = $this->model->lastestTransaction->price;
    }
}
