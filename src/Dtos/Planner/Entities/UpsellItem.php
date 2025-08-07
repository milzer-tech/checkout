<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class UpsellItem extends BaseDto
{
    /**
     * Create a new instance of class UpsellItem extends BaseDto
     */
    public function __construct(
        public string $name,
    ) {}
}
