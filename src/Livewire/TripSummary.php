<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class TripSummary extends Component
{
    public $isExpanded = false;

    public $tripDetails = [
        'title' => 'Palma de Mallorca',
    ];

    public $totalPrice = '1,234.56';

    public function toggleExpand()
    {
        $this->isExpanded = ! $this->isExpanded;
    }

    public function viewFullItinerary()
    {
        // Implement the logic to view the full itinerary
        // This could be a redirect or a modal display
    }

    public function render()
    {
        return view('checkout::trip-details-page.trip-summary');
    }
}
