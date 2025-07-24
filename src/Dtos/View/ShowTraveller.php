<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\View;

use Nezasa\Checkout\Dtos\BaseDto;

class ShowTraveller extends BaseDto
{
    /**
     * Create a new instance of ShowTraveller
     */
    public function __construct(
        public bool $isAdult,
        public bool $isFilled = false,
        public bool $isShowing = false,
        public ?int $age = null
    ) {}
}
