<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;

class ItineraryTransfer extends BaseDto
{
    /**
     * Create a new instance of ItineraryTransfer
     */
    public function __construct(
        public string $id,
        public string $startLocationName,
        public string $endLocationName,
        public CarbonImmutable $startDateTime,
        public CarbonImmutable $endDateTime,
        public bool $isPlaceholder,
        public ?string $name = null,
    ) {}

    /**
     * Get the title of the transfer.
     */
    public function getTitle(): string
    {
        return filled($this->name)
            ? $this->name
            : $this->startLocationName.' to '.$this->endLocationName;
    }
}
