<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\InitializeCheckoutDataAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Dtos\Planner\RequiredResponses;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Throwable;
use URL;

class TripDetailsPage extends BaseCheckoutComponent
{
    /**
     * The itinerary summary of the trip details page.
     */
    public ItinerarySummary $itinerary;

    /**
     * The url of the payment page.
     */
    public ?string $paymentPageUrl = null;

    public ?bool $checkingAvailability = null;

    public ?string $gateway = null;

    /**
     * The object containing the checkout data.
     */
    public RequiredResponses $result;

    public function mount(
        CallTripDetailsAction $callTripDetails,
        SummarizeItineraryAction $summerizeItinerary,
        InitializeCheckoutDataAction $initializeCheckoutData
    ): void {
        $this->result = $callTripDetails->run($this->itineraryId, $this->checkoutId);

        $this->model = $initializeCheckoutData->run(
            checkoutId: $this->checkoutId,
            itineraryId: $this->itineraryId,
            allocatedPax: $this->result->itinerary->allocatedPax
        );

        $this->itinerary = $summerizeItinerary->run(
            itineraryResponse: $this->result->itinerary,
            checkoutResponse: $this->result->checkout,
            addedRentalCarResponse: $this->result->addedRentalCars,
            addedUpsellItemsResponse: collect($this->result->addedUpsellItems),
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
            'contactRequirements' => $this->result->travelerRequirements->contact,
            'countryCodes' => $this->result->countryCodes,
            'allocatedPax' => $this->result->itinerary->allocatedPax,
            'passengerRequirements' => $this->result->travelerRequirements->passenger,
            'countriesResponse' => $this->result->countries,
            'prices' => $this->result->checkout->prices,
            'upsellItemsResponse' => $this->result->upsellItems,
            'addedUpsellItems' => $this->result->addedUpsellItems,
        ]);
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $prices
     */
    #[On('price-changed')]
    public function priceChanged(array $prices): void
    {
        $prices = ApplyPromoCodeResponse::from($prices);

        $this->itinerary->price = $prices->discountedPackagePrice ?? $prices->packagePrice;

        $this->itinerary->promoCodeResponse = $prices;
    }

    public function createPaymentPageUrl($gateway): void
    {
        $this->gateway = $gateway;

        $this->checkingAvailability = true;

        $this->dispatch('payment-selected', run: true);
    }

    #[On('availability-verified')]
    public function generatePaymentPageUrl(bool $result): void
    {
        if ($result) {
            $this->paymentPageUrl = URL::temporarySignedRoute(
                name: 'payment',
                expiration: now()->addMinutes(30),
                parameters: array_merge(
                    $this->getQueryParams(),
                    ['payment_method' => $this->gateway]
                )
            );
        }

        $this->checkingAvailability = false;
    }
}
