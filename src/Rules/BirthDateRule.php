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
     */
    public function __construct(public CarbonImmutable $startDate) {}

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

        try {
            if ($this->isAdult($attribute)) {
                return;
            }

            if ((int) $this->getBirthDate($attribute)->diffInYears($this->startDate) >= $maxAge) {
                $fail('checkout::input.validations.child_age')->translate(['age' => $maxAge]);
            }

        } catch (Throwable $exception) {
            $fail('checkout::checkout::input.validations.child_age', ['age' => $maxAge])->translate();
        }
    }

    /**
     * Determine if the traveller is an adult
     */
    private function isAdult(string $attribute)
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
        $birthDate = data_get($this->data, str($attribute)->beforeLast('.')->toString());

        return CarbonImmutable::create($birthDate['year'], $birthDate['month'], $birthDate['day']);
    }
}
