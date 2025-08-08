<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;

class ItineraryStay extends BaseDto
{
    public CarbonImmutable $checkOut;

    /**
     * Create a new instance of ItineraryStay.
     */
    public function __construct(
        public string $id,
        public string $name,
        public CarbonImmutable $checkIn,
        public int $nights,
        public ?AvailabilityEnum $availability = null,
    ) {
        $this->checkOut = $checkIn->copy()->addDays($nights);
    }
}
