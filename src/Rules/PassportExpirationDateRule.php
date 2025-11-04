<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Rules;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

final class PassportExpirationDateRule implements DataAwareRule, ValidationRule
{
    public function __construct(public CarbonImmutable $endDate) {}

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

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
        $date = data_get($this->data, str($attribute)->beforeLast('.')->toString());

        $carbon = CarbonImmutable::create($date['year'], $date['month'], $date['day']);

        if ($carbon->startOfDay()->isBefore($this->endDate->endOfDay())) {
            $fail('checkout::input.validations.passportExpirationDate')->translate();
        }
    }
}
