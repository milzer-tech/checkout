<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

final class InsuranceOfferDto extends BaseDto
{
    /**
     * Create a new instance of InsuranceOfferDto.
     *
     * @param  array<string>  $coverage
     */
    public function __construct(
        public string $id,
        public string $title,
        public Price $price,
        public array $coverage,
        public InsuranceTerms $terms = new InsuranceTerms
    ) {}

}
