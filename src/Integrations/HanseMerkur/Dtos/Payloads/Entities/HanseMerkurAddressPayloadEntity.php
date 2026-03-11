<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class HanseMerkurAddressPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurAddressPayloadEntity.
     */
    public function __construct(
        public string $countryIsoCode,
        public string $postalCode,
        public string $cityName,
        public string $streetName,
        public string $streetNumber,
    ) {}
}
