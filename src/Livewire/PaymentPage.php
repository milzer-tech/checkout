<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Actions\Checkout\FindCheckoutModelAction;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
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
        $this->initializeRequirements();

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
    protected function initializeRequirements(): void
    {
        $this->model = resolve(FindCheckoutModelAction::class)->run($this->getParams());

        $result = resolve(CallTripDetailsAction::class)->run($this->getParams());

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result->itinerary,
            checkoutResponse: $result->checkout,
            addedRentalCarResponse: $result->addedRentalCars,
            addedUpsellItemsResponse: collect($result->addedUpsellItems),
        );
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

        $result = resolve(PaymentInitiationHandler::class)->run(
            model: $this->model,
            price: $this->itinerary->price->downPayment,
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
