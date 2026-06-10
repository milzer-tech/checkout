<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class CredentialOnFilePayloadEntity extends BaseDto
{
    /**
     * Create a new instance of CredentialOnFilePayloadEntity.
     */
    public function __construct(
        public UnscheduledCredentialOnFilePayloadEntity $type = new UnscheduledCredentialOnFilePayloadEntity,
        public bool $initialPayment = true,
    ) {}
}
