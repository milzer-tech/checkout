<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurPaymentTypeEnum;

class HanseMerkurPaymentMethodPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurPaymentMethodPayloadEntity.
     */
    public function __construct(public HanseMerkurPaymentTypeEnum $type) {}
}
