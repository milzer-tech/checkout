<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryFlight;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Exceptions\CheckoutInUseException;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Handlers\DownPaymentCallBackHandler;
use Nezasa\Checkout\Payments\Handlers\RestPaymentCallBackHandler;
use Nezasa\Checkout\Support\CheckoutOwnership;

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

    /**
     * Indicates whether the result may no longer be shown to the current browser.
     */
    public bool $isExpired = false;

    public function mount(Request $request): void
    {
        $this->model = $this->transaction->checkout;

        $output = $this->model->rest_payment
            ? resolve(RestPaymentCallBackHandler::class)->run($this->transaction, $request)
            : resolve(DownPaymentCallBackHandler::class)->run($this->transaction, $request);

        // The payment is always processed above, only showing the result is limited.
        if ($this->isExpired = $this->hasExpired()) {
            return;
        }

        $this->output = $output;

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
        return view($this->isExpired ? 'checkout::blades.payment-result-expired' : 'checkout::blades.confirmation-page');
    }

    /**
     * Determine if the result may not be shown, as anyone holding the link could open it.
     * Like the checkout page, only the browser that owns the checkout sees it, and other browsers
     * are kept out while the owner is active. The rest payment is not bound to a browser, so its
     * result is only shown for the payment time after the transaction was created.
     *
     * @throws CheckoutInUseException
     */
    protected function hasExpired(): bool
    {
        $ownership = resolve(CheckoutOwnership::class);

        if ($ownership->isProtected($this->model)) {
            throw_if($ownership->isInUseElsewhere($this->model), CheckoutInUseException::class);

            return ! $ownership->isOwner($this->model);
        }

        return $this->transaction->created_at?->isAfter(now()->subMinutes(Config::integer('checkout.payment_ttl'))) !== true;
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
        $this->itinerary->flights->map(function (ItineraryFlight $item): void {
            $item->availability = $this->output->data[$item->id] ?? null;
        });
        $this->itinerary->transfers->map($callback);
        $this->itinerary->activities->map($callback);
        $this->itinerary->rentalCars->map($callback);
        $this->itinerary->upsellItems->map($callback);
        $this->itinerary->insurances->map($callback);

        $this->paid = $this->transaction->price;
    }

    protected function processInsuranceData(): void
    {
        try {
            $this->transaction->refresh();

            $checkoutData = InsuranceCheckoutData::checkoutDataArray($this->transaction->checkout->data);
            $offerRaw = InsuranceCheckoutData::getOffer($checkoutData);
            $insurance = $offerRaw ? InsuranceOfferDto::from($offerRaw) : null;
            if ($insurance instanceof InsuranceOfferDto) {
                $availability = data_get($this->transaction->result_data, 'insurance.isSuccessful', false)
                    ? AvailabilityEnum::Booked
                    : AvailabilityEnum::None;

                $this->itinerary->insurances = collect([
                    new InsuranceItem(id: $insurance->id, name: $insurance->title, availability: $availability),
                ]);

                return;
            }

            if (InsuranceCheckoutData::isDeclined($checkoutData)) {
                $this->itinerary->insurances = collect([
                    new InsuranceItem(
                        id: 'insurance-declined',
                        name: trans('checkout::page.booking_confirmation.insurance_declined'),
                        availability: AvailabilityEnum::None
                    ),
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
