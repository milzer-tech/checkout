<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;

class PromoCodeSection extends Component
{
    /**
     * The unique identifier for the checkout process.
     */
    #[Url]
    public string $checkoutId;

    /**
     * The promo code entered by the user.
     */
    public ?string $promoCode = null;

    /**
     * Indicates whether the user does not have a promo code.
     */
    public ?bool $notHavePromoCode = null;

    /**
     * The error message to display if the promo code is invalid.
     */
    public $isExpanded = true;

    /**
     * Indicates whether the section have been completed.
     */
    public bool $isCompleted = false;

    /**
     * The validation rules for the promo code input.
     */
    protected array $rules = [
        'promoCode' => 'required|min:1|max:256',
    ];

    /**
     * The prices data transfer object containing promo code information.
     */
    public ApplyPromoCodeResponse $prices;

    /**
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(): void
    {
        $this->promoCode = $this->prices->promoCode?->code;

        if ($this->hasCompleted()) {
            $this->collapse();
        }
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.promo-code-section');
    }

    /**
     * Apply the promo code.
     */
    public function save(): void
    {
        $this->validate();

        $this->deleteCurrentPromoCode();

        $response = NezasaConnector::make()->checkout()->applyPromoCode($this->checkoutId, $this->promoCode);

        if (! $response->ok()) {
            session()->flash('failedPromoCode', $response->array('problems')[0]['detail']);
        } else {
            $this->prices = $response->dto();
        }

        $this->hasCompleted();

        $this->dispatchPriceChangedEvent();
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function expand(): void
    {
        $this->isExpanded = true;
    }

    /**
     * Collapse the promo code section, hiding it from view.
     */
    public function collapse(): void
    {
        $this->isExpanded = false;
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->collapse();

        $this->dispatch('promoCode-done');
    }

    /**
     * Handle the case where the user does not have a promo code.
     */
    public function noPromoCode(): void
    {
        $this->notHavePromoCode = true;

        $this->next();
    }

    /**
     * Check if the promo code section is completed based on the presence of a promo code.
     */
    protected function hasCompleted(): bool
    {
        $this->isCompleted = $this->prices->promoCode?->code ?? false;

        return $this->isCompleted;
    }

    /**
     * Delete the promo code from the checkout process.
     */
    protected function deleteCurrentPromoCode(): void
    {
        NezasaConnector::make()->checkout()->deletePromoCode($this->checkoutId);

        $this->prices->promoCode = null;
        $this->prices->discountedPackagePrice = $this->prices->packagePrice;
    }

    /**
     * Check if the promo code section is expanded.
     */
    protected function markAsCompleted(): void
    {
        $this->isCompleted = true;
    }

    /**
     * Dispatch the 'price-changed' event with the updated prices.
     */
    protected function dispatchPriceChangedEvent(): void
    {
        $this->dispatch('price-changed', prices: $this->prices);
    }
}
