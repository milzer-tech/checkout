<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class EuPrrlResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the EuPrrlResponseEntity.
     */
    public function __construct(
        public bool $itineraryContentValidationEnabled = false,
        public ?EuPrrlComplianceResponseEntity $compliance = null,
    ) {}
}
