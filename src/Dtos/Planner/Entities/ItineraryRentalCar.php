<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;

class ItineraryRentalCar extends BaseDto
{
    /**
     * Create a new instance of ItineraryRentalCar
     */
    public function __construct(
        public string $id,
        public string $name,
        public CarbonImmutable $startDateTime,
        public CarbonImmutable $endDateTime,
        public bool $isPlaceholder,
        public ?AvailabilityEnum $availability = null,
    ) {}
}
