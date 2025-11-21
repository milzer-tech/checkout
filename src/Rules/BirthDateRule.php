<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Rules;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;

final class BirthDateRule implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * Create a new rule instance.
     *
     * @param  array<string, mixed>  $allocatedPax
     */
    public function __construct(public CarbonImmutable $startDate, public array $allocatedPax) {}

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $maxAge = Config::integer('checkout.distribution.max_child_age');

        $birthData = $this->getBirthDate($attribute);

        if (! $birthData instanceof CarbonImmutable) {
            return;
        }

        $age = (int) $birthData->diffInYears($this->startDate);

        if ($this->isAdult($attribute)) {
            if ($age <= $maxAge) {
                $fail('checkout::input.validations.adult_age')->translate(['age' => $maxAge]);
            }
        } else {
            if ($age >= $maxAge) {
                $fail('checkout::input.validations.child_age')->translate(['age' => $maxAge]);
            }

            $room = (int) str($attribute)->after('paxInfo.')->beforeLast('.')->toString();
            $traveler = (int) str($attribute)->before('.birthDate')->afterLast('.')->toString();
            $key = (int) $this->allocatedPax['rooms'][$room]['adults'] - $traveler;

            if ($this->allocatedPax['rooms'][$room]['childAges'][$key] !== $age) {
                $fail('checkout::input.validations.child_age_diff')->translate([
                    'age' => $this->allocatedPax['rooms'][$room]['childAges'][$key],
                ]);
            }
        }

    }

    /**
     * Determine if the traveller is an adult
     */
    private function isAdult(string $attribute): ?bool
    {
        return data_get(
            $this->data,
            str($attribute)->beforeLast('.')->beforeLast('.')->append('.showTraveller')->toString()
        )?->isAdult;
    }

    /**
     * Get the birth date of the traveller
     */
    private function getBirthDate(string $attribute): ?CarbonImmutable
    {
        try {
            $birthDate = data_get($this->data, str($attribute)->beforeLast('.')->toString());

            $result = CarbonImmutable::create($birthDate['year'], $birthDate['month'], $birthDate['day']);
        } finally {
            return $result ?? null;
        }
    }
}
