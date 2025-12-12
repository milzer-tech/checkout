<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ContactRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;
use Nezasa\Checkout\Models\Checkout;

class ContactDetails extends BaseCheckoutComponent
{
    /**
     * The data for the contact details.
     *
     * @var array<string, string|int>
     */
    public array $contact;

    /**
     * Contact requirements for the checkout.
     */
    public ContactRequirementEntity $contactRequirements;

    /**
     * The country calling codes for the contact details.
     */
    public CountryCodesResponse $countryCodes;

    /**
     * The countries response that holds the list of countries.
     */
    public CountriesResponse $countriesResponse;

    /**
     * Initialize the component with the contact details.
     */
    public function mount(): void
    {
        /** @phpstan-ignore-next-line */
        $this->contact = $this->model->data->get('contact');

        $this->defineDefaultValues();
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.contact-details');
    }

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
    public function save(): void
    {
        $validatedData = $this->validate();

        $this->model->updateData(['contact' => $validatedData['contact']]);

        $this->markAsCompletedAdnCollapse(Section::Contact);

        $this->dispatch(Section::Contact->value);
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
            'mobilePhone' => ['array'],
            'mobilePhone.countryCode' => [
                'numeric',
                Rule::in($this->countryCodes->callingCodes->map(fn ($item): string => $item->callingCode)),

            ],
            'mobilePhone.phoneNumber' => ['numeric'],
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

        foreach ($this->contactRequirements->getVisibleFields() as $name => $item) {
            if ($item->isRequired()) {
                if ($name === 'mobilePhone') {
                    $rules["$name.phoneNumber"] = array_merge(['required'], $rules["$name.phoneNumber"]);

                    continue;
                }

                $rules[$name] = array_merge(['required'], $rules[$name]);
            }
        }

        return array_combine(
            array_map(fn (string $key): string => "contact.$key", array_keys($rules)),
            array_values($rules)
        );
    }

    /**
     * Defines default values for the contact data.
     */
    public function defineDefaultValues(): void
    {
        // if the country code is not set, set the default country code.
        if ($this->contactRequirements->mobilePhoneDefaultCountryCode
            && ! data_get($this->contact, 'mobilePhone.countryCode')) {
            data_set(
                $this->contact,
                'mobilePhone.countryCode',
                $this->contactRequirements->mobilePhoneDefaultCountryCode->callingCode
            );
        }

        $defaultCountry = $this->countriesResponse->countries->firstWhere('preferred', true);

        if ($defaultCountry && ! isset($this->contact['country'])) {
            $this->contact['country'] = "$defaultCountry->iso_code-$defaultCountry->name";
        }
    }

    /**
     * Returns the validation messages for the contact details.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return collect($this->rules())
            ->mapWithKeys(function ($item, $key): array {
                $translatedKey = str_replace('contact.', '', $key);
                $finalKey = "checkout::input.attributes.$translatedKey";

                if (in_array($translatedKey, ['mobilePhone.countryCode', 'mobilePhone.phoneNumber'])) {
                    $finalKey = str($translatedKey)->afterLast('.')
                        ->prepend('checkout::input.attributes.')
                        ->toString();
                }

                return [$key => trans($finalKey)];
            })
            ->toArray();
    }
}
