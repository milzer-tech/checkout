<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class InsuranceOffersResult extends BaseDto
{
    /**
     * Create a new instance of InsuranceOffersResult.
     *
     * @param  array<int, InsuranceOfferDto>  $offers
     * @param  array<string|int, mixed>  $meta
     */
    public function __construct(
        // to check if the insurance provider is available or not
        public bool $isSuccessful,
        // Only this property is exposed to the browser.
        public array $offers = [],
        public InsuranceTerms $terms = new InsuranceTerms,
        // This property's content is stored in the database and accessible after the payment is successful.
        public array $meta = [],
        public ?string $errorMessage = null,
    ) {}

}
