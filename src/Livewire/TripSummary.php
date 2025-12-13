<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\VerifyAvailabilityAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class TripSummary extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * The total price of the itinerary.
     */
    public Price $total;

    /**
     * The down payment for the itinerary.
     */
    public Price $downPayment;

    /**
     * Whether to show the price breakdown.
     */
    public bool $showPriceBreakdown = false;

    public string $nezasaPlannerUrl;

    public function mount(): void
    {
        $this->nezasaPlannerUrl = config('checkout.nezasa.base_url').'/itineraries/'.$this->itineraryId;

        $this->showPriceBreakdown = $this->itinerary->price->externallyPaidCharges->externallyPaidCharges->isNotEmpty();

        $this->total = $this->itinerary->price->discountedPackagePrice;
        $this->downPayment = $this->itinerary->price->downPayment;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.trip-summary');
    }

    /**
     * Collapse or expand the price breakdown.
     */
    public function togglePriceBreakdown(): void
    {
        $this->showPriceBreakdown = ! $this->showPriceBreakdown;
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $price
     */
    #[On('price-changed')]
    public function priceChanged(array $price): void
    {
        $this->itinerary->price = ApplyPromoCodeResponse::from($price);

        $this->total = $this->itinerary->price->discountedPackagePrice;

        $this->dispatch('price-updated', price: $price);
    }

    /**
     * Handle the summary updated event.
     */
    #[On('summary-updated')]
    public function summaryUpdated(): void
    {
        $result = resolve(CallTripDetailsAction::class)->run(checkout_params());

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result->itinerary,
            checkoutResponse: $result->checkout,
            addedRentalCarResponse: $result->addedRentalCars,
            addedUpsellItemsResponse: collect($result->addedUpsellItems),
        );

        $this->dispatch('price-updated', $this->itinerary->price);
    }

    /**
     * Handle the promo code applied event.
     */
    #[On('payment-selected')]
    public function verifyAvailability(): void
    {
        $this->dispatch(
            event: 'availability-verified',
            result: resolve(VerifyAvailabilityAction::class)->run($this->checkoutId, $this->itinerary),
        );
    }

    /**
     * Add insurance to the itinerary.
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, float|string>  $price
     */
    #[On('insurance-selected')]
    public function addInsurance(array $item, array $price): void
    {
        $this->itinerary->insurances = new Collection([InsuranceItem::from($item)]);

        $this->total->amount = $this->itinerary->price->discountedPackagePrice->amount + intval($price['amount']);
        $this->downPayment->amount = $this->itinerary->price->downPayment->amount + intval($price['amount']);
    }

    /**
     * Remove insurance from the itinerary.
     */
    #[On('insurance-declined')]
    public function removeInsurance(): void
    {
        $this->itinerary->insurances = new Collection;

        $this->total = $this->itinerary->price->discountedPackagePrice;
        $this->downPayment = $this->itinerary->price->downPayment;
    }
}
