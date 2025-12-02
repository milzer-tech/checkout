<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Enums\Section;

class PaymentOptionsSection extends BaseCheckoutComponent
{
    /**
     * Available payment options.
     *
     * @var array<int, PaymentOption>
     */
    public array $options = [];

    /**
     * Create a new instance of the component.
     */
    public function mount(GetPaymentProviderAction $getPaymentProviderAction): void
    {
        $this->options = $getPaymentProviderAction->run();
    }

    /**
     * Render the view for the payment options section.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.payment-options-section');
    }

    /**
     * Listen for the 'traveller-processed' event to determine if the promo code section should be expanded or completed.
     */
    #[On(Section::TermsAndConditions->value)]
    public function listen(): void
    {
        $this->expand(Section::PaymentOptions);
    }
}
