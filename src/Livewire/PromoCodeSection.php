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

        NezasaConnector::make()->checkout()->deletePromoCode($this->checkoutId);

        $response = NezasaConnector::make()->checkout()->applyPromoCode($this->checkoutId, $this->promoCode);

        if (! $response->ok()) {
            $this->prices->promoCode = null;
            $this->prices->discountedPackagePrice = $this->prices->packagePrice;

            session()->flash('failedPromoCode', $response->array('problems')[0]['detail']);
        } else {
            $this->prices = $response->dto();
        }

        $this->dispatch('price-changed', prices: $this->prices);
    }
}
