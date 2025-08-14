<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Dtos\View\PaymentOption;
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
}
