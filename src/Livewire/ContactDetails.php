<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;

class ContactDetails extends Component
{
    public $email = '';

    public $phone = '';

    public $contactExpanded = false;

    public $contactDetailsCompleted = false;

    protected $rules = [
        'email' => 'required|email',
        'phone' => 'required|min:10',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $validatedData = $this->validate();
        $this->contactDetailsCompleted = true;
        $this->contactExpanded = false;

        // Here you would typically save the data to your database
        // For now, we'll just emit an event to notify the parent component
        $this->dispatch('contactDetailsSaved', $validatedData);
    }

    public function editContact()
    {
        $this->contactExpanded = true;
        $this->contactDetailsCompleted = false;
    }

    public function render()
    {
        return view('checkout::trip-details-page.contact-details');
    }
}
