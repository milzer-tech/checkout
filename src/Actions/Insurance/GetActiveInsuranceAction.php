<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Insurance;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;

final class GetActiveInsuranceAction
{
    /**
     * Check if there is an active insurance provider.
     */
    public function run(): ?InsuranceContract
    {
        $active = [];

        foreach (Config::get('checkout.insurance_provider') as $insurance) {
            if (! in_array(InsuranceContract::class, (array) class_implements($insurance))) {
                throw new InvalidArgumentException(
                    "the insurance $insurance is not an instance of InsuranceContract"
                );
            }

            if ($insurance::isActive()) {
                $active[] = new $insurance;
            }
        }

        if (count($active) > 1 || (count($active) === 1 && Config::boolean('checkout.insurance.vertical.active'))) {
            throw new InvalidArgumentException('Only one insurance provider can be active at a time.');
        }

        /** @phpstan-ignore-next-line  */
        return $active[0] ?? null;
    }
}
