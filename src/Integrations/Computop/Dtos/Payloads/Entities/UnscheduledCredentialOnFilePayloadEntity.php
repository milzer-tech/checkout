<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class UnscheduledCredentialOnFilePayloadEntity extends BaseDto
{
    /**
     * Create a new instance of UnscheduledCredentialOnFilePayloadEntity.
     */
    public function __construct(
        public string $unscheduled = 'CIT',
    ) {}
}
