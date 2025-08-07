<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;

class RentalCarResponseEntity extends BaseDto
{
    /**
     * Create a new instance of RentalCarResponseEntity.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/planner-api-v1.html#tag/Rental-Cars/paths/~1v1~1itineraries~1%7BitineraryId%7D~1rental-cars/get
     *
     * @note: There are more properties available in the API response, but we only include the ones we need.
     */
    public function __construct(
        public string $id,
        public string $name,
        public CarbonImmutable $pickupDateTime,
        public CarbonImmutable $dropoffDateTime,
        public int $rentalDurationInDays,
        public bool $isPlaceholder,
    ) {}
}
