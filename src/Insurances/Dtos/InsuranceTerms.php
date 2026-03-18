<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class InsuranceTerms extends BaseDto
{
    /**
     * Create a new instance of InsuranceTerms.
     *
     * @param  array<int, InsuranceTerm>  $terms
     */
    public function __construct(
        public ?string $text = null,
        public ?string $checkboxText = null,
        public array $terms = []
    ) {}

}
