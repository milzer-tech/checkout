<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class BookInsuranceOfferDto extends BaseDto
{
    /**
     * Create a new instance of BookInsuranceOfferDto.
     *
     * @param  array<string,  mixed>  $meta
     */
    public function __construct(
        public InsuranceOfferDto $selectedOffer,
        public CreateInsuranceOffersDto $createdOfferDto,
        public array $meta = []
    ) {}
}
