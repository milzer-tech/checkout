<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;

class UpsellItem extends BaseDto
{
    /**
     * Create a new instance of class UpsellItem extends BaseDto
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?AvailabilityEnum $availability = null,
    ) {}
}
