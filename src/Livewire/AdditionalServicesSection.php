<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class AdditionalServicesSection extends Component
{
    public $selectedServices = [];

    public $services = [
        [
            'id' => 'airport_transfer',
            'name' => 'Airport Transfer',
            'description' => 'Private transfer from/to the airport',
            'price' => 75,
        ],
        [
            'id' => 'breakfast',
            'name' => 'Breakfast Package',
            'description' => 'Daily breakfast included',
            'price' => 25,
        ],
        [
            'id' => 'wifi',
            'name' => 'Premium WiFi',
            'description' => 'High-speed WiFi throughout your stay',
            'price' => 15,
        ],
        [
            'id' => 'spa',
            'name' => 'Spa Access',
            'description' => 'Access to spa facilities',
            'price' => 50,
        ],
    ];

    public function toggleService($serviceId)
    {
        if (in_array($serviceId, $this->selectedServices)) {
            $this->selectedServices = array_diff($this->selectedServices, [$serviceId]);
        } else {
            $this->selectedServices[] = $serviceId;
        }

        $this->dispatch('servicesUpdated', ['services' => $this->selectedServices]);
    }

    public function render()
    {
        return view('checkout::trip-details-page.additional-services-section');
    }
}
