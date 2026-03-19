<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class HanseMerkurAllocationPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurAllocationPayloadEntity.
     */
    public function __construct(
        public int $insuredPersonId,
        public bool $coInsured = false
    ) {}
}
