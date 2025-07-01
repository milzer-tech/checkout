<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class PaymentOptionsSection extends Component
{
    public $selectedPaymentMethod = 'credit_card';

    public $cardNumber = '';

    public $cardHolderName = '';

    public $expiryDate = '';

    public $cvv = '';

    public $billingAddress = '';

    public $billingCity = '';

    public $billingCountry = '';

    public $billingPostalCode = '';

    protected $rules = [
        'cardNumber' => 'required|digits:16',
        'cardHolderName' => 'required|min:3',
        'expiryDate' => 'required|regex:/^\d{2}\/\d{2}$/',
        'cvv' => 'required|digits:3',
        'billingAddress' => 'required',
        'billingCity' => 'required',
        'billingCountry' => 'required',
        'billingPostalCode' => 'required',
    ];

    public function selectPaymentMethod($method)
    {
        $this->selectedPaymentMethod = $method;
        $this->dispatch('paymentMethodSelected', ['method' => $method]);
    }

    public function savePaymentDetails()
    {
        $this->validate();

        // Here you would typically process the payment
        // For now, we'll just dispatch an event
        $this->dispatch('paymentDetailsSaved', [
            'method' => $this->selectedPaymentMethod,
            'details' => [
                'cardNumber' => $this->cardNumber,
                'cardHolderName' => $this->cardHolderName,
                'expiryDate' => $this->expiryDate,
                'billingAddress' => $this->billingAddress,
                'billingCity' => $this->billingCity,
                'billingCountry' => $this->billingCountry,
                'billingPostalCode' => $this->billingPostalCode,
            ],
        ]);
    }

    public function render()
    {
        return view('checkout::trip-details-page.payment-options-section');
    }
}
