<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Handlers\WidgetInitiationHandler;

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
        return view('checkout::trip-details-page.payment-page');
    }

    public function goBack()
    {

        return to_route('traveler-details', $this->getQueryParams());
    }

    /**
     * Initialize the requirements for the payment page.
     */
    protected function initializeRequirements(): void
    {
        $this->model = Checkout::whereCheckoutId($this->checkoutId)->firstOrFail();

        $result = resolve(CallTripDetailsAction::class)->run($this->itineraryId, $this->checkoutId);

        $this->itinerary = resolve(SummarizeItineraryAction::class)->run(
            itineraryResponse: $result['itinerary'],
            checkoutResponse: $result['checkout'],
            addedRentalCarResponse: $result['addedRentalCars'],
            addedUpsellItemsResponse: collect($result['addedUpsellItems']),
        );
    }

    /**
     * Handle the payment process by preparing the payment data and initializing the payment gateway.
     */
    protected function handlePayment(): void
    {
        $gateway = PaymentGatewayEnum::from(decrypt(request()->query('payment_method')));

        abort_unless($gateway->isWidget(), 404, 'The payment gateway is not supported.');

        $this->payment = resolve(WidgetInitiationHandler::class)->run(
            model: $this->model,
            data: new PaymentPrepareData(
                contact: ContactInfoPayloadEntity::from($this->model->data['contact']),
                price: $this->itinerary->price,
                checkoutId: $this->checkoutId,
                itineraryId: $this->itineraryId,
                origin: $this->origin,
                lang: $this->model->lang
            ),
            gateway: $gateway
        );
    }
}
