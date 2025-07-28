<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;

class PromoCodeSection extends BaseCheckoutComponent
{
    /**
     * The promo code entered by the user.
     */
    public ?string $promoCode = null;

    /**
     * Indicates whether the user does not have a promo code.
     */
    public ?bool $notHavePromoCode = null;

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

        if ($this->promoCode) {
            $this->markAsCompleted(Section::Promo);
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
            $this->markAsCompleted(Section::Promo);
        }

        $this->dispatchPriceChangedEvent();
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->collapse(Section::Promo);

        $this->dispatch('promoCode-done');
    }

    /**
     * Handle the case where the user does not have a promo code.
     */
    public function noPromoCode(): void
    {
        $this->notHavePromoCode = true;

        $this->markAsCompleted(Section::Promo);

        $this->next();
    }

    /**
     * Delete the promo code from the checkout process.
     */
    protected function deleteCurrentPromoCode(): void
    {
        NezasaConnector::make()->checkout()->deletePromoCode($this->checkoutId);

        $this->prices->promoCode = null;
        $this->prices->discountedPackagePrice = $this->prices->packagePrice;

        $this->markAsNotCompleted(Section::Promo);
    }

    /**
     * Dispatch the 'price-changed' event with the updated prices.
     */
    protected function dispatchPriceChangedEvent(): void
    {
        $this->dispatch('price-changed', prices: $this->prices);
    }
}
