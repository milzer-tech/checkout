<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\ContactRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\CountryCallingCodeResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Models\Checkout;

class ContactDetails extends Component
{
    /**
     * The unique identifier for the checkout process.
     */
    #[Url]
    public string $checkoutId;

    /**
     * The data for the contact details.
     */
    public array $contact;

    /**
     * Indicates whether the contact details form is expanded or not.
     */
    public bool $contactExpanded = true;

    /**
     * Contact requirements for the checkout.
     */
    public ContactRequirementEntity $contactRequirements;

    /**
     * Initialize the component with the contact details.
     */
    public function mount(): void
    {
        $this->contact = Checkout::query()
            ->firstOrCreate(['checkout_id' => $this->checkoutId])
            ->data
            ->get('contact', []);

        $this->validate();

        $this->contactExpanded = false;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.contact-details');
    }

    /**
     * The country calling codes for the contact details.
     *
     * @var Collection<int, CountryCallingCodeResponseEntity>
     */
    public CountryCodesResponse $countryCodes;

    /**
     * Update the contact details when a field is changed.
     */
    public function updated(string $name, mixed $value)
    {
        Checkout::query()
            ->firstOrCreate(['checkout_id' => $this->checkoutId])
            ->updateData($name, $value);
    }

    /**
     * Save the contact details.
     */
    public function save()
    {
        $validatedData = $this->validate();

        Checkout::query()
            ->firstOrCreate(['checkout_id' => $this->checkoutId])
            ->updateData('contact', $validatedData['contact']);

        $this->contactExpanded = false;
    }

    /**
     * Cancel the contact details edit and collapse the form.
     */
    public function editContact()
    {
        $this->contactExpanded = true;
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
