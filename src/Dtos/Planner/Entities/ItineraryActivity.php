<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Illuminate\Support\Stringable;
use Nezasa\Checkout\Dtos\BaseDto;

class ItineraryActivity extends BaseDto
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
