<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites;

use Spatie\LaravelData\Data;

class CountryCallingCodeResponseEntity extends Data
{
    /**
     * Create a new instance of the CountryCallingCodeResponseEntity
     *
     * @link https://docs.tripbuilder.app/Mo9reezaehiengah/location-api-v1.html#/paths/~1countries~1calling-codes/get
     */
    public function __construct(
        public string $isoCode,
        public string $name,
        public string $callingCode,
    ) {}
}
