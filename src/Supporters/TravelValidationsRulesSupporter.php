<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\In;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\CountryResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Livewire\TravelerDetails;
use Nezasa\Checkout\Rules\BirthDateRule;
use Nezasa\Checkout\Rules\PassportExpirationDateRule;

final readonly class TravelValidationsRulesSupporter
{
    public function __construct(private TravelerDetails $travelerDetails) {}

    /**
     * Get the validation rules for the traveler details.
     *
     * @return array<string, array<Enum|string|Rule|PassportExpirationDateRule|BirthDateRule|In>>
     */
    public function rules(): array
    {
        $rules = $this->getDefaultRules();

        $rules = $this->addCustomizeRules($rules);

        foreach ($this->travelerDetails->passengerRequirements->all() as $name => $item) {
            if ($item->isRequired()) {
                $rules[$name] = array_merge(['required'], $rules[$name]);

                if ($name === 'birthDate' || $name === 'passportExpirationDate') {
                    $rules["$name.day"] = array_merge(['required'], $rules["$name.day"]);
                    $rules["$name.month"] = array_merge(['required'], $rules["$name.month"]);
                    $rules["$name.year"] = array_merge(['required'], $rules["$name.year"]);
                }
            }
        }

        return array_combine(
            array_map(fn (string $key): string => 'paxInfo.*.*.'.$key, array_keys($rules)),
            array_values($rules)
        );
    }

    /**
     * @return array<string, array<Enum|string|Rule|PassportExpirationDateRule|BirthDateRule|In>>
     */
    public function getDefaultRules(): array
    {
        $countries = $this->travelerDetails->countriesResponse
            ->countries
            ->map(fn (CountryResponseEntity $item): string => $item->iso_code.'-'.$item->name)
            ->all();

        return [
            'firstName' => ['string', 'max:255'],
            'lastName' => ['string', 'max:255'],
            'secondOrAdditionalName' => ['string', 'max:255'],
            'passportNr' => ['string', 'max:255'],
            'nationality' => ['string', Rule::in($countries)],
            'gender' => [new Enum(GenderEnum::class)],
            'birthDate' => ['array'],
            'birthDate.day' => ['integer', 'min:1', 'max:31'],
            'birthDate.month' => ['integer', 'min:1', 'max:12'],
            'birthDate.year' => [
                'integer',
                'min:1900',
                'max:'.date('Y'), /** @phpstan-ignore-next-line */
                new BirthDateRule($this->travelerDetails->itinerary->startDate, $this->travelerDetails->model->data->get('allocatedPax', [])),
            ],
            'passportExpirationDate' => ['array'],
            'passportExpirationDate.day' => ['integer', 'min:1', 'max:31'],
            'passportExpirationDate.month' => ['integer', 'min:1', 'max:12'],
            'passportExpirationDate.year' => [
                'integer',
                'min:'.date('Y'),
                'max:'.intval(date('Y')) + 30,
                new PassportExpirationDateRule($this->travelerDetails->itinerary->endDate),
            ],
            'passportIssuingCountry' => ['string', Rule::in($countries)],
            'postalCode' => ['string', 'max:20'],
            'city' => ['string', 'max:255'],
            'country' => ['string', Rule::in($countries)],
            'countryCode' => ['string', 'max:10'],
            'street1' => ['string', 'max:255'],
            'street2' => ['string', 'max:255'],
        ];
    }

    /**
     * Apply the customization rules per the configuration.
     *
     * @param  array<string, array<Enum|string|Rule|PassportExpirationDateRule|BirthDateRule|In>>  $rules
     * @return array<string, array<Enum|string|Rule|PassportExpirationDateRule|BirthDateRule|In>>
     */
    public function addCustomizeRules(array $rules): array
    {
        if (Config::boolean('checkout::cuba-travel.active')) {
            $keys = Config::collection('checkout::cuba-travel.reasons')->keys()->map(fn ($key): string => strval($key));

            $rules['travel_reason'] = [
                'required',
                Rule::in($keys->toArray()),
            ];
        }

        return $rules;
    }
}
