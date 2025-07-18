<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\CountryResponseEntity;

class CountriesResponse extends BaseDto
{
    /**
     * Create a new instance of the CountriesResponse
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/location-api-v1.html#/paths/~1areas~1%7BrefId%7D~1pictures/get
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     *
     * @param  Collection<int, CountryResponseEntity>  $callingCodes
     */
    public function __construct(
        public Collection $countries,

    ) {}
}
