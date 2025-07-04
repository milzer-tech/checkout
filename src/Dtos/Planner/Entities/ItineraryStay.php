<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Illuminate\Support\Stringable;
use Nezasa\Checkout\Dtos\BaseDto;

class ItineraryStay extends BaseDto
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
