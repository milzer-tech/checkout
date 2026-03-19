<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class InsuranceTerms extends BaseDto
{
    /**
     * Create a new instance of InsuranceTerms.
     * // values can be HTML
     *
     * @param  array<int, string>  $conditions
     */
    public function __construct(
        public ?string $text = null,
        public ?string $checkboxText = null,
        public array $conditions = []
    ) {}

    public function getKey(): string
    {
        return md5($this->toJson());
    }
}
