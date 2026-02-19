<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Handlers\DownPaymentCallBackHandler;
use Nezasa\Checkout\Payments\Handlers\RestPaymentCallBackHandler;

class PaymentResultPage extends BaseCheckoutComponent
{
    public Transaction $transaction;

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
     * The output from the payment widget.
     */
    public PaymentOutput $output;

    public function mount(Request $request): void
    {
        $this->model = $this->transaction->checkout;

        $this->output = $this->model->rest_payment
                ? resolve(RestPaymentCallBackHandler::class)->run($this->transaction, $request)
                : resolve(DownPaymentCallBackHandler::class)->run($this->transaction, $request);

        $this->initializeRequirements();

        foreach ($this->model->data['paxInfo'] as $room) {
            foreach ($room as $pax) {
                $this->travelers[] = $pax['firstName'].' '.$pax['lastName'];
            }
        }

        $this->isExpanded = true;
    }

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
        $result = resolve(CallTripDetailsAction::class)->run($this->getParams());

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result->itinerary,
            checkoutResponse: $result->checkout,
            addedRentalCarResponse: $result->addedRentalCars,
            addedUpsellItemsResponse: collect($result->addedUpsellItems),
            checkout: $this->model
        );

        $callback = fn ($item) => $item->availability = $this->output->data[$item->id] ?? AvailabilityEnum::None;

        $this->itinerary->stays->map($callback);
        $this->itinerary->flights->map($callback);
        $this->itinerary->transfers->map($callback);
        $this->itinerary->activities->map($callback);
        $this->itinerary->rentalCars->map($callback);
        $this->itinerary->upsellItems->map($callback);
        $this->itinerary->insurances->map($callback);
    }
}
