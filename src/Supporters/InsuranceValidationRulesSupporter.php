<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use Nezasa\Checkout\Actions\Insurance\GetActiveInsuranceAction;

final readonly class InsuranceValidationRulesSupporter
{
    private const array PRESENCE_RULES = [
        'nullable',
        'required',
    ];

    /**
     * @return array<string, array<int, string>>
     */
    public function contactRules(): array
    {
        return $this->withoutPresenceRules(
            app(GetActiveInsuranceAction::class)->run()?->getContactValidationRules() ?? []
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function passengerRules(): array
    {
        return $this->withoutPresenceRules(
            app(GetActiveInsuranceAction::class)->run()?->getPassengerValidationRules() ?? []
        );
    }

    /**
     * @param  array<string, array<int, string>>  $rules
     * @return array<string, array<int, string>>
     */
    private function withoutPresenceRules(array $rules): array
    {
        foreach ($rules as $field => $fieldRules) {
            $filteredRules = array_values(array_filter(
                $fieldRules,
                fn (string $rule): bool => ! in_array(str($rule)->before(':')->lower()->toString(), self::PRESENCE_RULES, true)
            ));

            if ($filteredRules === []) {
                unset($rules[$field]);

                continue;
            }

            $rules[$field] = $filteredRules;
        }

        return $rules;
    }
}
