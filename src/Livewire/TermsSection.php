<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TermsAndConditionsResponseEntity;

class TermsSection extends BaseCheckoutComponent
{
    /**
     * The terms and conditions response containing the terms and conditions.
     */
    public TermsAndConditionsResponseEntity $termsAndConditions;

    /**
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(): void {}

    /**
     * Render the component view.
     */
    public function render(): View
    {      /** @phpstan-ignore-next-line */
        return view('checkout::blades.terms-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Insurance);
    }

    /**
     * Listen for the additional service section, expanding the terms and conditions section.
     */
    #[On(Section::AdditionalService->value)]
    public function listen(): void
    {
        $this->expand(Section::TermsAndConditions);
    }
}
