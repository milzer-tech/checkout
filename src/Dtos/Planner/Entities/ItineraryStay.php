<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Illuminate\Support\Stringable;
use Spatie\LaravelData\Data;

class ItineraryStay extends Data
{
    public CarbonImmutable $checkOut;

    /**
     * Create a new instance of ItineraryStay.
     */
    public function __construct(
        public Stringable $name,
        public CarbonImmutable $checkIn,
        public int $nights,
    ) {
        $this->checkOut = $checkIn->copy()->addDays($nights);
    }
}
