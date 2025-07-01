<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class ConfirmationPage extends Component
{
    public $tripDetails = [
        'title' => 'Palma de Mallorca',
    ];

    public $totalPrice = '1,234.56';

    public $bookingReference = 'arqwjf82ow';

    public $orderDate = 'Mon, 2 Feb 2025';

    public $travelers = ['Hillary Cash', 'Johnny Cash', 'Josh Cash', 'Jake Cash'];

    public function mount()
    {
        // Example data - replace with actual data from your database
        $this->tripDetails = [
            'title' => 'Palma de Mallorca',
            'image' => '/images/42912e66-032b-40fd-ab59-fb16306d9ad5.png',
        ];
        $this->totalPrice = 1000;
        $this->bookingReference = 'ABC123';
        $this->orderDate = 'March 15, 2024';
        $this->travelers = [
            'John Doe',
            'Jane Doe',
            'Child 1',
            'Child 2',
        ];
    }

    public function viewFullItinerary()
    {
        // Implement view full itinerary logic
    }

    public function printBookingConfirmation()
    {
        // Implement print booking confirmation logic
    }

    public function viewCancellationPolicy()
    {
        // Implement the logic to view the cancellation policy
    }

    public function contactSupport()
    {
        // Implement the logic to contact support
    }

    public function render()
    {
        return view('checkout::trip-details-page.confirmation-page');
    }
}
