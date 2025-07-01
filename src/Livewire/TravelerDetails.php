<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class TravelerDetails extends Component
{
    public $travelerExpanded = true;

    public $showSecondTraveler = false;

    public $travelers = [];

    protected $rules = [
        'travelers.*.firstName' => 'required|min:2',
        'travelers.*.secondName' => 'nullable|min:2',
        'travelers.*.lastName' => 'required|min:2',
        'travelers.*.nationality' => 'required',
        'travelers.*.gender' => 'required',
        'travelers.*.dateOfBirth' => 'required|date',
        'travelers.*.passportNumber' => 'required|min:6',
        'travelers.*.passportIssuingCountry' => 'required',
        'travelers.*.passportExpiry' => 'required|date|after:today',
    ];

    public function mount()
    {
        $this->initializeTravelers();
    }

    public function initializeTravelers()
    {
        $this->travelers = [
            [
                'firstName' => '',
                'secondName' => '',
                'lastName' => '',
                'nationality' => '',
                'gender' => '',
                'dateOfBirth' => '',
                'passportNumber' => '',
                'passportIssuingCountry' => '',
                'passportExpiry' => '',
            ],
        ];
    }

    public function addTraveler()
    {
        $this->travelers[] = [
            'firstName' => '',
            'secondName' => '',
            'lastName' => '',
            'nationality' => '',
            'gender' => '',
            'dateOfBirth' => '',
            'passportNumber' => '',
            'passportIssuingCountry' => '',
            'passportExpiry' => '',
        ];
    }

    public function save()
    {
        // No validation for now
        $this->travelerExpanded = false;
        $this->dispatch('enablePromoCodeSection');
    }

    public function render()
    {
        return view('checkout::trip-details-page.traveler-details');
    }
}
