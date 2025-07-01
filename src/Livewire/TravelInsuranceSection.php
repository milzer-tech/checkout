<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class TravelInsuranceSection extends Component
{
    public $selectedInsurance = null;

    public $insuranceOptions = [
        [
            'id' => 'basic',
            'name' => 'Basic Coverage',
            'description' => 'Essential coverage for medical emergencies and trip cancellation',
            'price' => 50,
        ],
        [
            'id' => 'standard',
            'name' => 'Standard Coverage',
            'description' => 'Comprehensive coverage including medical, trip cancellation, and lost baggage',
            'price' => 75,
        ],
        [
            'id' => 'premium',
            'name' => 'Premium Coverage',
            'description' => 'Full coverage with additional benefits like emergency evacuation and 24/7 support',
            'price' => 100,
        ],
    ];

    public function selectInsurance($insuranceId)
    {
        $this->selectedInsurance = $insuranceId;
        $this->dispatch('insuranceSelected', ['insuranceId' => $insuranceId]);
    }

    public function render()
    {
        return view('checkout::trip-details-page.travel-insurance-section');
    }
}
