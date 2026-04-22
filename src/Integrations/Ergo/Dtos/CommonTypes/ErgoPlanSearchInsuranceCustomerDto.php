<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

/**
 * {@code InsuranceCustomer} on ERV_InsurancePlanSearchRQ (residence only).
 */
class ErgoPlanSearchInsuranceCustomerDto extends Data
{
    public function __construct(
        public string $ResidenceCountryCode,
    ) {}
}
