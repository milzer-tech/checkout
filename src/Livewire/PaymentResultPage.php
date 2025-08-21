<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Handlers\WidgetCallBackHandler;

class PaymentResultPage extends BaseCheckoutComponent
{
    /**
     * Holds the names of the travelers.
     *
     * @var array<int, string>
     */
    public array $travelers = [];

    public ItinerarySummary $itinerary;

    public PaymentOutput $output;

    public function mount(Request $request): void
    {
        $this->model = Checkout::with('lastestTransaction')
            ->whereCheckoutId($this->checkoutId)
            ->firstOrFail();

        $this->output = resolve(WidgetCallBackHandler::class)->run($this->model->lastestTransaction, $request);

        $this->initializeRequirements();

        foreach ($this->model->data['paxInfo'] as $room) {
            foreach ($room as $pax) {
                $this->travelers[] = $pax['firstName'].' '.$pax['lastName'];
            }
        }
    }

    public function render(): View
    {
        return view('checkout::trip-details-page.confirmation-page');
    }

    /**
     * Initialize the requirements for the payment page.
     */
    protected function initializeRequirements(): void
    {
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
