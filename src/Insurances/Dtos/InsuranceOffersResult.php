<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

final class InsuranceOffersResult
{
    /**
     * Create a new instance of InsuranceOffersResult.
     *
     * @param  array<int, InsuranceOfferDto>  $offers
     * @param  array<int, ???>  $terms
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
