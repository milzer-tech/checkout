<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class PaymentPage extends Component
{
    public $totalPrice;

    public function mount()
    {
        // Initialize any necessary data
        $this->totalPrice = 1000; // Example value, replace with actual data
    }

    public function render()
    {
        return view('checkout::trip-details-page.payment-page');
    }

    public function goBack()
    {
        // Implement the logic to navigate back
        return redirect()->back();
    }

    public function goToPayment()
    {
        // Implement the logic to navigate to the payment page
        return redirect()->route('confirmation');
    }
}
