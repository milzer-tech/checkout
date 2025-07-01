<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class PromoCodeSection extends Component
{
    public $promoCode = '';

    public $isValid = false;

    public $discount = 0;

    public $error = '';

    public $enabled = false;

    protected $rules = [
        'promoCode' => 'required|min:3|max:20',
    ];

    protected $listeners = ['enablePromoCodeSection' => 'enableSection'];

    public function enableSection()
    {
        $this->enabled = true;
    }

    public function updatedPromoCode()
    {
        $this->validateOnly('promoCode');
        $this->error = '';
        $this->isValid = false;
        $this->discount = 0;
    }

    public function applyPromoCode()
    {
        $this->validate();

        // Here you would typically check the promo code against your database
        // For now, we'll just simulate a valid code
        if ($this->promoCode === 'WELCOME10') {
            $this->isValid = true;
            $this->discount = 10;
            $this->error = '';
            $this->dispatch('promoCodeApplied', ['discount' => $this->discount]);
        } else {
            $this->isValid = false;
            $this->discount = 0;
            $this->error = 'Invalid promo code';
        }
    }

    public function render()
    {
        return view('checkout::trip-details-page.promo-code-section');
    }
}
