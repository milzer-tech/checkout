<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;

class InsuranceSection extends BaseCheckoutComponent
{
    /**
     * The prices data transfer object containing promo code information.
     */
    public ApplyPromoCodeResponse $prices;

    /**
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(): void {}

    /**
     * Render the component view.
     */
    public function render(): View
    {      /** @phpstan-ignore-next-line */
        return view('checkout::blades.insurance-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Insurance);
    }
}
