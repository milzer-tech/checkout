<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurStatusEnum;

final class HanseMerkurPolicyDetailResponseEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurPolicyDetailResponseEntity.
     */
    public function __construct(
        public string $policyNumber,
        public HanseMerkurStatusEnum $status,
    ) {}

}
