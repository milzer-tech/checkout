<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class ItineraryRentalCar extends Data
{
    /**
     * Create a new instance of ItineraryRentalCar
     */
    public function __construct(
        public string $name,
        public CarbonImmutable $startDateTime,
        public CarbonImmutable $endDateTime
    ) {}
}
