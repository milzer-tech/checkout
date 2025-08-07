<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RentalCarResponseEntity;

class AddedRentalCarResponse extends BaseDto
{
    /**
     * Create a new instance of AddedRentalCarResponse.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/planner-api-v1.html#tag/Rental-Cars/paths/~1v1~1itineraries~1%7BitineraryId%7D~1rental-cars/get
     *
     * @param  Collection<int, RentalCarResponseEntity>  $rentalCars
     */
    public function __construct(public Collection $rentalCars = new Collection) {}
}
