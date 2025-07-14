<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\CountryCallingCodeResponseEntity;

class CountryCodesResponse extends BaseDto
{
    /**
     * Create a new instance of the CountryCodesResponse
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/location-api-v1.html#/paths/~1countries~1calling-codes/get
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     *
     * @param  Collection<int, CountryCallingCodeResponseEntity>  $callingCodes
     */
    public function __construct(
        public Collection $callingCodes,

    ) {}
}
