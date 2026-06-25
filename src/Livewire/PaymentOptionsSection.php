<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\OnRequestResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RegulatoryInformationResponse;

class PaymentOptionsSection extends BaseCheckoutComponent
{
    /**
     * Available payment options.
     *
     * @var array<int, PaymentOption>
     */
    public array $options = [];

    /**
     * The regulatory information for the payment options.
     */
    public RegulatoryInformationResponse $regulatoryInformation;

    /**
     * The prices data transfer object containing promo code information.
     */
    public PriceResponse $price;

    /**
     * Whether the latest availability check marked the itinerary as on-request.
     */
    #[Reactive]
    public bool $isOnRequest = false;

    /**
     * Whether the customer accepted the on-request confirmation.
     */
    public bool $acceptedOnRequestTerms = false;

    /**
     * Whether to show the on-request confirmation validation error.
     */
    public bool $showOnRequestTermsError = false;

    /**
     * Create a new instance of the component.
     */
    public function mount(GetPaymentProviderAction $getPaymentProviderAction): void
    {
        $this->options = $getPaymentProviderAction->run();

        if ($this->model->rest_payment) {
            $this->isExpanded = true;
        }

        $this->acceptedOnRequestTerms = $this->hasAcceptedOnRequestTerms();
    }

    /**
     * Render the view for the payment options section.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.payment-options-section');
    }

    public function requiresOnRequestConfirmation(): bool
    {
        return $this->isOnRequest
            && isset($this->regulatoryInformation)
            && $this->regulatoryInformation->onRequest?->confirmationEnabled === true;
    }

    /**
     * Persist and validate the on-request confirmation checkbox.
     */
    public function toggleOnRequestTerms(bool $value): void
    {
        $this->acceptedOnRequestTerms = $value;

        if ($this->regulatoryInformation->onRequest instanceof OnRequestResponseEntity) {
            $this->model->updateData([
                'acceptedTerms.'.$this->regulatoryInformation->onRequest->getConfirmationKey() => $value,
            ]);
        }

        $this->showOnRequestTermsError = $value === false && $this->requiresOnRequestConfirmation();

        $this->dispatch('on-request-confirmation-updated', accepted: $value);
    }

    #[On('on-request-confirmation-required')]
    public function showOnRequestConfirmationError(): void
    {
        if (! $this->requiresOnRequestConfirmation()) {
            return;
        }

        $this->showOnRequestTermsError = true;
    }

    private function hasAcceptedOnRequestTerms(): bool
    {
        if (! isset($this->regulatoryInformation)) {
            return false;
        }

        if (! $this->regulatoryInformation->onRequest instanceof OnRequestResponseEntity) {
            return false;
        }

        $data = json_decode(json_encode($this->model->data, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
        $acceptedTerms = is_array($data) ? data_get($data, 'acceptedTerms', []) : [];

        return ($acceptedTerms[$this->regulatoryInformation->onRequest->getConfirmationKey()] ?? false) === true;
    }

    /**
     * Listen for the 'traveller-processed' event to determine if the promo code section should be expanded or completed.
     */
    #[On(Section::TravelInformation->value)]
    public function listen(): void
    {
        $this->expand(Section::PaymentOptions);
    }

    /**
     * Reset the section.
     *
     * @param  array<int, string>  $sections
     */
    #[On('sections-reset')]
    public function resetSection(array $sections): void
    {
        if (! in_array(Section::PaymentOptions->value, $sections, true)) {
            return;
        }

        $this->isCompleted = false;
        $this->isExpanded = false;
    }
}
