<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\View;

use Nezasa\Checkout\Dtos\BaseDto;

class PaymentOption extends BaseDto
{
    /**
     * The payment option ID.
     */
    public function __construct(
        public string $name,
        public bool $isSelected = false,
        public ?string $description = null,
        public ?string $icon = null,
    ) {}
}
