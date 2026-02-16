<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Actions\Checkout\FindCheckoutModelAction;
use Nezasa\Checkout\Actions\Checkout\InitializeCheckoutDataAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Dtos\Planner\RequiredResponses;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
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

    /**
     * Indicates whether the user is checking the availability of the payment gateway.
     */
    public ?bool $checkingAvailability = null;

    /**
     * The gateway selected by the user.
     */
    public ?string $gateway = null;

    /**
     * The object containing the checkout data.
     */
    public RequiredResponses $result;

    public function mount(
        CallTripDetailsAction $callTripDetails,
        FindCheckoutModelAction $findCheckoutModelAction,
        SummarizeItineraryAction $summerizeItinerary,
        InitializeCheckoutDataAction $initializeCheckoutData
    ): void {
        $this->result = $callTripDetails->run(params: $this->getParams());

        $this->model = $initializeCheckoutData->run(
            model: $findCheckoutModelAction->run(params: $this->getParams()),
            params: $this->getParams(),
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
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.index', [
            'contactRequirements' => $this->result->travelerRequirements->contact,
            'countryCodes' => $this->result->countryCodes,
            'allocatedPax' => $this->result->itinerary->allocatedPax,
            'passengerRequirements' => $this->result->travelerRequirements->passenger,
            'countriesResponse' => $this->result->countries,
            'prices' => $this->result->checkout->prices,
            'upsellItemsResponse' => $this->result->upsellItems,
            'addedUpsellItems' => $this->result->addedUpsellItems,
            'regulatoryInformation' => $this->result->regulatoryInformation,
        ]);
    }

    public function createPaymentPageUrl(string $gateway): void
    {
        $this->gateway = $gateway;

        foreach ($this->model->data['status'] as $name => $section) {
            if (Section::from($name)->isPaymentOptions()) {
                continue;
            }

            if ($section['isCompleted'] === false) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'title' => trans('checkout::page.trip_details.error'),
                    'message' => trans('checkout::exceptions.please_complete_this_section').': '.Section::from($name)->label(),
                ]);

                return;
            }
        }

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
                    $this->getParams()->toArray(),
                    ['payment_method' => $this->gateway]
                )
            );
        }

        $this->checkingAvailability = false;
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $price
     */
    #[On('price-updated')]
    public function priceChanged(array $price): void
    {
        $this->itinerary->price = PriceResponse::from($price);
    }
}
