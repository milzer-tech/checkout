<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class InsuranceTerm extends BaseDto
{
    /**
     * Create a new instance of InsuranceTerm.
     */
    public function __construct(
        public string $text,
        public ?string $link = null,
    ) {}

}
