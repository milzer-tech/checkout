<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class HanseMerkurContactDataPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurContactDataPayloadEntity.
     */
    public function __construct(
        public string $email,
        public HanseMerkurAddressPayloadEntity $address,
        public ?string $telephone = null,
    ) {}
}
