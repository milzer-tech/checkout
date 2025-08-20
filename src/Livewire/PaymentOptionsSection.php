<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;

class PaymentOptionsSection extends BaseCheckoutComponent
{
    public array $options = [];

    public function mount(): void
    {

        $this->options[] = new PaymentOption('Oppwa', PaymentGatewayEnum::Oppwa);

        foreach ($this->options as $option) {
            if ($option->name == $this->model->payment_method) {
                $option->isSelected = true;
            }
        }
    }

    /**
     * Render the view for the payment options section.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.payment-options-section');
    }

    /**
     * Listen for the 'traveller-processed' event to determine if the promo code section should be expanded or completed.
     */
    #[On(Section::AdditionalService->value)]
    public function listen(): void
    {
        $this->expand(Section::PaymentOptions);
    }
}
