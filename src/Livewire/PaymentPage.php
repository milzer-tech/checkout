<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaInitiation;

class PaymentPage extends BaseCheckoutComponent
{
    public $totalPrice = 100;

    public $itinerary;

    public PaymentAsset $payment;

    public function mount(CallTripDetailsAction $callTripDetails, SummarizeItineraryAction $summerizeItinerary): void
    {
        $this->model = Checkout::whereCheckoutId($this->checkoutId)->firstOrFail();

        $result = $callTripDetails->run($this->itineraryId, $this->checkoutId);

        $this->itinerary = $summerizeItinerary->run(
            itineraryResponse: $result['itinerary'],
            checkoutResponse: $result['checkout'],
            addedRentalCarResponse: $result['addedRentalCars'],
            addedUpsellItemsResponse: collect($result['addedUpsellItems']),
        );

        $payment = new OppwaInitiation;

        $init = $payment->prepare($this->model, $this->itinerary->price);

        $this->model->transactions()->create([
            'gateway' => $init->gateway,
            'prepare_data' => (array) $init->persistentData,
            'status' => PaymentStatusEnum::Pending,
        ]);

        $this->payment = $payment->getAssets($init, route('payment-result', $this->getQueryParams()));
    }

    public function render(): View
    {
        return view('checkout::trip-details-page.payment-page');
    }

    public function goBack()
    {
        return to_route('traveler-details', $this->getQueryParams());
    }
}
