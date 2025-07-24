<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ContactRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;
use Nezasa\Checkout\Models\Checkout;
use Throwable;

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
     * Indicates whether the traveler details have been completed.
     */
    public bool $isCompleted = false;

    /**
     * Initialize the component with the contact details.
     */
    public function mount(): void
    {
        $this->contact = Checkout::query()
            ->firstOrCreate(['checkout_id' => $this->checkoutId])
            ->data
            ?->get('contact', []) ?? [];

        try {
            $this->validate();
            $this->contactExpanded = false;
            $this->isCompleted = true;
        } catch (Throwable $e) {
        }
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
     */
    public CountryCodesResponse $countryCodes;

    /**
     * The countries response that holds the list of countries.
     */
    public CountriesResponse $countriesResponse;

    /**
     * Update the contact details when a field is changed.
     */
    public function updated(string $name, mixed $value): void
    {
        $this->validate([
            $name => $this->rules()[$name],
        ]);

        $job = new SaveTraverDetailsJob(checkoutId: $this->checkoutId, name: $name, value: $value);

        dispatch($job);
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
        $this->isCompleted = true;

        $this->dispatch('contact-stored');
    }

    /**
     * Cancel the contact details edit and collapse the form.
     */
    public function editContact(): void
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
            'street1' => ['string', 'max:255'],
            'street2' => ['string', 'max:255'],
            'countryCode' => ['string', 'max:10'],

        ];

        foreach ($this->contactRequirements as $name => $item) {
            $rules[$name] = array_merge($item->isRequired() ? ['required'] : ['nullable'], $rules[$name]);
        }

        return array_combine(
            array_map(fn ($key) => "contact.$key", array_keys($rules)),
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
            ->mapWithKeys(function ($item, $key) {
                $translatedKey = str_replace('contact.', '', $key);

                return [$key => strtolower(trans("checkout::input.attributes.$translatedKey"))];
            })
            ->toArray();
    }
}
