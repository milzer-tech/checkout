<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Rules;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

final class PassportExpirationDateRule implements DataAwareRule, ValidationRule
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
    public function __construct(public CarbonImmutable $endDate) {}

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
        try {
            $date = data_get($this->data, str($attribute)->beforeLast('.')->toString());

            $carbon = CarbonImmutable::create($date['year'], $date['month'], $date['day']);

            if ($carbon->startOfDay()->isBefore($this->endDate->endOfDay())) {
                $fail('checkout::input.validations.passportExpirationDate')->translate();
            }
        } catch (Throwable $exception) {
            $fail('checkout::input.validations.passportExpirationDate')->translate();
        }
    }
}
