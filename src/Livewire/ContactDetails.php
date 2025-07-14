<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Validation\Rules\Enum;
use Livewire\Component;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\ContactRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;

class ContactDetails extends Component
{
    public array $contact;

    public $contactExpanded = true;

    public $contactDetailsCompleted = false;

    public ContactRequirementEntity $contactRequirements;

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

    /**
     * returns the validation rules for the contact details.
     *
     * @return array<string, array<string|Enum>>
     */
    protected function rules(): array
    {
        $rules = [
            'firstName' => ['string', 'max:255'],
            'lastName' => ['string', 'max:255'],
            'companyName' => ['string', 'max:255'],
            'gender' => [new Enum(GenderEnum::class)],
            'email' => ['email', 'max:255'],
            'mobilePhone' => ['string', 'max:20'],
            'postalCode' => ['string', 'max:20'],
            'city' => ['string', 'max:255'],
            'state' => ['string', 'max:255'],
            'country' => ['string', 'max:255'],
            'taxNumber' => ['string', 'max:255'],
            'localIdNumber' => ['string', 'max:255'],
            'address1' => ['array'],
            'address1.country' => ['string', 'max:255'],
            'address1.countryCode' => ['string', 'max:10'],
            'address1.city' => ['string', 'max:255'],
            'address1.postalCode' => ['string', 'max:20'],
            'address1.street1' => ['string', 'max:255'],
            'address1.street2' => ['string', 'max:255'],
            'address1.region' => ['string', 'max:255'],
            'address2' => ['array'],
            'address2.country' => ['string', 'max:255'],
            'address2.countryCode' => ['string', 'max:10'],
            'address2.city' => ['string', 'max:255'],
            'address2.postalCode' => ['string', 'max:20'],
            'address2.street1' => ['string', 'max:255'],
            'address2.street2' => ['string', 'max:255'],
            'address2.region' => ['string', 'max:255'],
        ];

        foreach ($this->contactRequirements as $name => $item) {
            $required = $item->isRequired() ? ['required'] : ['nullable'];

            $rules[$name] = array_merge($required, $rules[$name]);

            if ($name === 'address1' || $name === 'address2') {
                $rules["$name.country"] = array_merge($required, $rules["$name.country"]);
                $rules["$name.city"] = array_merge($required, $rules["$name.city"]);
                $rules["$name.postalCode"] = array_merge($required, $rules["$name.postalCode"]);
                $rules["$name.street1"] = array_merge($required, $rules["$name.street1"]);
            }
        }

        return array_combine(
            array_map(fn ($key) => 'contact.'.$key, array_keys($rules)),
            array_values($rules)
        );
    }

    /**
     * Returns the validation messages for the contact details.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return collect($this->rules())
            ->reject(fn ($item, $key) => $key === 'contact.address1' || $key === 'contact.address2')
            ->mapWithKeys(function ($item, $key) {
                $translatedKey = str_replace('contact.', '', $key);

                return [$key => strtolower(trans("checkout::input.attributes.$translatedKey"))];
            })
            ->toArray();
    }
}
