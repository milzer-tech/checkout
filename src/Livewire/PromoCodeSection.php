<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;

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

        NezasaConnector::make()->checkout()->deletePromoCode($this->checkoutId);

        $response = NezasaConnector::make()->checkout()->applyPromoCode($this->checkoutId, $this->promoCode);

        if (! $response->ok()) {
            session()->flash('failedPromoCode', $response->array('problems')[0]['detail']);

            return;
        }

        session()->flash('appliedPromoCode', $response->dto()->decreasePercent());

        $this->dispatch('promoCode-applied', ApplyPromoCodeResponse: $response->dto());
    }
}
