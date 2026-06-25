<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class EuPrrlComplianceResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the EuPrrlComplianceResponseEntity.
     *
     * @param  array<int, string>  $reasons
     */
    public function __construct(
        public ?bool $compliant = null,
        public array $reasons = [],
    ) {}
}
