<?php

namespace Nezasa\Checkout\Livewire;

use Livewire\Component;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\ContactRequirementEntity;

class ContactDetails extends Component
{
    public array $contact;

    public $contactExpanded = true;

    public $contactDetailsCompleted = false;

    public ContactRequirementEntity $contactRequirements;

    protected $rules = [
        'contact' => 'array',
        'contact.email' => 'required|email|max:4',
        'contact.phone' => 'required|max:1',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $validatedData = $this->validate();
        dd(
            $validatedData
        );
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

    public function rules()
    {
        dd(
            $this->contactRequirements
        );
    }
}
