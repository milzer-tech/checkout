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
        public bool $adult,
        public bool $show,
        public ?int $age = null
    ) {}
}
