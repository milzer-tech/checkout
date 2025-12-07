<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class InsuranceItem extends BaseDto
{
    /**
     * Create a new instance of InsuranceItem.
     */
    public function __construct(
        public string $id,
        public string $name,
    ) {}
}
