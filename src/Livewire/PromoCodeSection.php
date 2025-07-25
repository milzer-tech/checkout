<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class PromoCodeSection extends Component
{
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
    }
}
