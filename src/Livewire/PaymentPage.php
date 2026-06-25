<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Actions\Checkout\FindCheckoutModelAction;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TravelInformation\LoadTravelInformationAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\Handlers\InsuranceHandler;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RegulatoryInformationResponse;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Handlers\PaymentInitiationHandler;

class PaymentPage extends BaseCheckoutComponent
{
    /**
     * The itinerary summary of the payment page.
     */
    public ItinerarySummary $itinerary;

    /**
     * The PaymentAsset to show required info.
     */
    public PaymentAsset $payment;

    /**
     * Mount the component and initialize requirements.
     */
    public function mount(): void
    {
        if (! $this->initializeRequirements()) {
            return;
        }

        $this->handlePayment();
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.payment-page');
    }

    /**
     * Go back to the traveller details page.
     */
    public function goBack(): void
    {
        $this->redirect(
            route('traveler-details', $this->getParams()->toArray())
        );
    }

    /**
     * Initialize the requirements for the payment page.
     */
    protected function initializeRequirements(): bool
    {
        $this->model = resolve(FindCheckoutModelAction::class)->run($this->getParams());

        $result = resolve(CallTripDetailsAction::class)->run($this->getParams());

        if ($result->regulatoryInformation->blocksCheckout()) {
            $this->redirect(route('traveler-details', $this->getParams()->toArray()));

            return false;
        }

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result->itinerary,
            checkoutResponse: $result->checkout,
            addedRentalCarResponse: $result->addedRentalCars,
            addedUpsellItemsResponse: collect($result->addedUpsellItems),
            checkout: $this->model
        );

        if ($this->requiresTravelInformationConfirmation($result->regulatoryInformation)
            && ! $this->hasValidTravelInformationConfirmation()) {
            $this->redirect(route('traveler-details', $this->getParams()->toArray()));

            return false;
        }

        return true;
    }

    private function requiresTravelInformationConfirmation(RegulatoryInformationResponse $regulatoryInformation): bool
    {
        return $regulatoryInformation->travelInformation?->confirmationEnabled === true
            && Config::boolean('checkout.integrations.passolution.active')
            && filled(Config::string('checkout.integrations.passolution.token'));
    }

    private function hasValidTravelInformationConfirmation(): bool
    {
        if (data_get($this->model->data, 'travel_information_confirmed') !== true) {
            return false;
        }

        $currentHash = resolve(LoadTravelInformationAction::class)
            ->confirmationHash($this->model, $this->itinerary->destinationCountries);

        return data_get($this->model->data, 'travel_information_confirmation_hash') === $currentHash;
    }

    /**
     * Handle the payment process by preparing the payment data and initializing the payment gateway.
     */
    protected function handlePayment(): void
    {
        // verify the payment method
        /** @var PaymentContract $className */
        $className = collect(resolve(GetPaymentProviderAction::class)->run())
            ->where('name', decrypt(request()->query('payment_method')))
            ->firstOrFail()
            ->decryptClassName();

        $this->itinerary->price->showPaymentPrice = resolve(InsuranceHandler::class)
            ->paymentPriceWithSelectedOffer($this->itinerary->price->showPaymentPrice, $this->model->data);

        $result = resolve(PaymentInitiationHandler::class)->run(
            model: $this->model,
            price: $this->itinerary->price->showPaymentPrice,
            gateway: new $className
        );

        if ($result instanceof PaymentAsset) {
            $this->payment = $result;
        }

        if ($result instanceof Uri) {
            redirect($result);
        }
    }
}
