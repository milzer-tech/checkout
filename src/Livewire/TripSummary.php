<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\VerifyAvailabilityAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Contracts\NezasaComponentDtoContract;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class TripSummary extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * Whether to show the price breakdown.
     */
    public bool $showPriceBreakdown = false;

    /**
     * Indicates whether the destination has a cost.
     */
    public bool $hasDestinationCost = false;

    public ?string $separateInsurancePaymentNotice = null;

    public function mount(): void
    {
        $this->determineHasExternalCharges();

        $this->determinePriceBreakdownDisplay();
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
        $this->itinerary->price = PriceResponse::from($price);

        $this->dispatch('price-updated', price: $price);
    }

    /**
     * Handle the summary updated event.
     */
    #[On('summary-updated')]
    public function summaryUpdated(): void
    {
        $result = resolve(CallTripDetailsAction::class)->run($this->getParams());

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result->itinerary,
            checkoutResponse: $result->checkout,
            addedRentalCarResponse: $result->addedRentalCars,
            addedUpsellItemsResponse: collect($result->addedUpsellItems),
            checkout: $this->model
        );

        $this->dispatch('price-updated', $this->itinerary->price);
    }

    /**
     * Handle the promo code applied event.
     */
    #[On('payment-selected')]
    public function verifyAvailability(): void
    {
        $availability = resolve(VerifyAvailabilityAction::class)->runWithSummary($this->getParams(), $this->itinerary);

        $this->dispatch(
            event: 'availability-verified',
            result: $availability['bookable'],
            isOnRequest: $availability['isOnRequest'],
        );
    }

    /**
     * Add insurance to the itinerary.
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, float|string>  $price
     */
    #[On('insurance-selected')]
    public function addInsurance(
        array $item,
        array $price,
        bool $shouldAddPriceToItinerary = true,
        ?string $separatePaymentNotice = null
    ): void {
        $this->itinerary->insurances = new Collection([InsuranceItem::from($item)]);
        $this->separateInsurancePaymentNotice = $separatePaymentNotice;

        $insurancePrice = $shouldAddPriceToItinerary ? Price::from($price)->amount : 0.0;
        $totalPrice = $this->itinerary->price->discountedPackagePrice;
        $paymentPrice = $this->itinerary->price->downPayment;

        $this->itinerary->price->showTotalPrice = new Price(
            amount: $totalPrice->amount + $insurancePrice,
            currency: $totalPrice->currency,
        );
        $this->itinerary->price->showPaymentPrice = new Price(
            amount: $paymentPrice->amount + $insurancePrice,
            currency: $paymentPrice->currency,
        );

        resolve(VerifyAvailabilityAction::class)->run($this->getParams(), $this->itinerary);

        $this->dispatch('price-updated', $this->itinerary->price);
    }

    /**
     * Remove insurance from the itinerary.
     */
    #[On('insurance-declined')]
    public function removeInsurance(): void
    {
        $this->itinerary->insurances = new Collection;
        $this->separateInsurancePaymentNotice = null;

        $totalPrice = $this->itinerary->price->discountedPackagePrice;
        $paymentPrice = $this->itinerary->price->downPayment;

        $this->itinerary->price->showTotalPrice = new Price(
            amount: $totalPrice->amount,
            currency: $totalPrice->currency,
        );
        $this->itinerary->price->showPaymentPrice = new Price(
            amount: $paymentPrice->amount,
            currency: $paymentPrice->currency,
        );

        resolve(VerifyAvailabilityAction::class)->run($this->getParams(), $this->itinerary);

        $this->dispatch('price-updated', $this->itinerary->price);
    }

    /**
     * Check if the destination has external charges.
     */
    public function determineHasExternalCharges(): void
    {
        $this->hasDestinationCost = $this->itinerary->price->externallyPaidCharges->externallyPaidCharges->isNotEmpty();
    }

    /**
     * Hide the price breakdown if the price is not affected by it.
     */
    public function determinePriceBreakdownDisplay(): void
    {
        if ($this->hasDestinationCost) {
            $this->showPriceBreakdown = true;
        }

        if ($this->itinerary->price->showPaymentPrice->amount < $this->itinerary->price->showTotalPrice->amount) {
            $this->showPriceBreakdown = true;
        }

        if ($this->model->rest_payment) {
            $this->showPriceBreakdown = true;
        }
    }

    /**
     * Get the URL to replace the component with.
     */
    public function getUrlToReplaceComponent(NezasaComponentDtoContract $componentDto): string
    {
        $type = $componentDto->getType()->isTransport() ? 'flight' : $componentDto->getType()->toLower();

        $baseUrl = $this->origin === 'IBE'
            ? config('checkout.nezasa.ibe_base_url')
            : config('checkout.nezasa.base_url');

        return $this->goTo === 'smartplanner'
            ? $baseUrl.'?nz-url='.urlencode("/itinerary-apps/smartplanner/$this->itineraryId?nz-lang=$this->lang&openDrawer=$type&componentId=".$componentDto->getId())
            : $baseUrl.'/itineraries/'.$this->itineraryId;
    }
}
