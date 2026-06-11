<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Concerns;

use Nezasa\Checkout\Actions\Insurance\GetActiveInsuranceAction;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Integrations\Nezasa\Enums\TravelerRequirementFieldEnum;

trait AppliesActiveInsuranceTravelerRequirements
{
    /**
     * @param  callable(InsuranceContract): array<string, TravelerRequirementFieldEnum>  $requirementsForInsurance
     */
    private function applyActiveInsuranceTravelerRequirements(callable $requirementsForInsurance): void
    {
        $insurance = app(GetActiveInsuranceAction::class)->run();

        if ($insurance === null) {
            return;
        }

        $this->applyTravelerRequirementOverrides($requirementsForInsurance($insurance));
    }

    /**
     * @param  array<string, TravelerRequirementFieldEnum>  $requirements
     */
    private function applyTravelerRequirementOverrides(array $requirements): void
    {
        foreach ($requirements as $field => $requirement) {
            if (property_exists($this, $field)) {
                $this->{$field} = $requirement;
            }
        }
    }
}
