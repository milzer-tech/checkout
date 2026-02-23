<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared;

use Nezasa\Checkout\Dtos\BaseDto;

class AddressEntity extends BaseDto
{
    /**
     * Create a new instance of AddressEntity.
     */
    public function __construct(
        public ?string $country = null,
        public ?string $countryCode = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public ?string $street1 = null,
        public ?string $street2 = null,
        public ?string $region = null
    ) {}

    /**
     * Get the postal code as an integer.
     */
    public function getNumericPostalCode(): ?int
    {
        return is_numeric($this->postalCode) ? (int) $this->postalCode : null;
    }

    /**
     * Get the two-letter country code.
     */
    public function getCountryCode(): ?string
    {
        return str($this->country)->before('-')->toString();
    }
}
