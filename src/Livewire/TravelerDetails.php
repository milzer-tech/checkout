<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;

class TravelerDetails extends Component
{
    /**
     * The PaxAllocationResponseEntity that holds the allocation of travelers.
     */
    public PaxAllocationResponseEntity $allocatedPax;

    public $travelerExpanded = true;

    public $showSecondTraveler = false;

    public $travelers = [];

    protected $rules = [

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
