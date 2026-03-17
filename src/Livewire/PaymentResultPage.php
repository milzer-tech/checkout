<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
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

    public Price $paid;

    public function mount(Request $request): void
    {

        $this->model = $this->transaction->checkout;

        $this->output = $this->model->rest_payment
            ? resolve(RestPaymentCallBackHandler::class)->run($this->transaction, $request)
            : resolve(DownPaymentCallBackHandler::class)->run($this->transaction, $request);

        $this->initializeRequirements();
        $this->processInsuranceData();

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

        $fallBackStatus = $this->model->rest_payment ? null : AvailabilityEnum::None;
        $callback = fn ($item) => $item->availability = $this->output->data[$item->id] ?? $fallBackStatus;

        $this->itinerary->stays->map($callback);
        $this->itinerary->flights->map($callback);
        $this->itinerary->transfers->map($callback);
        $this->itinerary->activities->map($callback);
        $this->itinerary->rentalCars->map($callback);
        $this->itinerary->upsellItems->map($callback);
        $this->itinerary->insurances->map($callback);

        $this->paid = $this->itinerary->price->downPayment;
    }

    protected function processInsuranceData(): void
    {
        try {
            $insurance = $this->transaction->checkout->data['insurance']
                ? InsuranceOfferDto::from($this->transaction->checkout->data['insurance'])
                : null;
            if ($insurance) {
                $availability = data_get($this->transaction->result_data, 'insurance.isSuccessful', false)
                    ? AvailabilityEnum::Booked
                    : AvailabilityEnum::None;

                $this->itinerary->insurances = collect([
                    new InsuranceItem(id: $insurance->id, name: $insurance->title, availability: $availability),
                ]);
            }
            $this->paid->amount += $insurance->price->amount;
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
