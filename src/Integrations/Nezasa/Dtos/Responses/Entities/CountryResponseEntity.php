<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class CountryResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the CountryResponseEntity
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/location-api-v1.html#/paths/~1countries/get
     */
    public function __construct(
        public string $iso_code,
        public string $name,
        public bool $preferred,
    ) {}
}
