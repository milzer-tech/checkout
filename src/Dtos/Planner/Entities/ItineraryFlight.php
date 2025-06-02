<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class ItineraryFlight extends Data
{
    /**
     * Create a new instance of ItineraryFlight
     */
    public function __construct(
        public string $startLocationName,
        public string $endLocationName,
        public CarbonImmutable $startDateTime,
        public CarbonImmutable $endDateTime
    ) {}
}
