<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Dtos\View\PaymentOption;

class PaymentOptionsSection extends BaseCheckoutComponent
{
    public array $options = [];

    public function mount(): void
    {
        $this->options[] = new PaymentOption('Oppwa');

        foreach ($this->options as $option) {
            if ($option->name == $this->model->payment_method) {
                $option->isSelected = true;
            }
        }
    }

    public function select(string $name): void
    {
        if (collect($this->options)->where('name', $name)->isNotEmpty()) {
            $this->model->update([
                'payment_method' => $name,
            ]);
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
