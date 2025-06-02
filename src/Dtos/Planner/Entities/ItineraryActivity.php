<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Illuminate\Support\Stringable;
use Spatie\LaravelData\Data;

class ItineraryActivity extends Data
{
    /**
     * Create a new instance of ItineraryActivity.
     */
    public function __construct(
        public Stringable $name,
        public CarbonImmutable $startDateTime,
        public CarbonImmutable $endDateTime,
    ) {}
}
