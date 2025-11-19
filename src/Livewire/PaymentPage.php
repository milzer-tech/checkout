<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Actions\Checkout\FindCheckoutModelAction;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Handlers\WidgetInitiationHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    public function mount(GetPaymentProviderAction $getPaymentProviderAction): void
    {
        $this->initializeRequirements();

        $this->handlePayment($getPaymentProviderAction);

        if ($this->model->lastestTransaction?->gateway === 'Invoice') {
            redirect($this->payment->scripts['url']);
        }
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.payment-page');
    }

    public function goBack(): RedirectResponse
    {
        return to_route('traveler-details', $this->getQueryParams());
    }

    /**
     * Initialize the requirements for the payment page.
     */
    protected function initializeRequirements(): void
    {
        $this->model = resolve(FindCheckoutModelAction::class)->run($this->checkoutId, $this->itineraryId);

        $result = resolve(CallTripDetailsAction::class)->run($this->itineraryId, $this->checkoutId);

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
    protected function handlePayment(GetPaymentProviderAction $getPaymentProviderAction): void
    {
        $name = decrypt(request()->query('payment_method'));

        $className = Config::collection('checkout.payment.widget', [])
            /** @phpstan-ignore-next-line  */
            ->filter(fn ($callback, $initiation): bool => $initiation::name() === $name)
            ->keys()
            ->firstOrFail();

        $this->payment = resolve(WidgetInitiationHandler::class)->run(
            model: $this->model,
            data: new PaymentPrepareData(
                contact: ContactInfoPayloadEntity::from($this->model->data['contact']),
                price: $this->itinerary->price,
                checkoutId: $this->checkoutId,
                itineraryId: $this->itineraryId,
                origin: $this->origin,
                lang: request()->input('lang', 'en'),
            ),
            gateway: (string) $className
        );
    }
}
