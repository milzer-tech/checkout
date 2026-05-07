<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Dtos\Contracts\NezasaComponentDtoContract;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\ComponentEnum;

class ItineraryStay extends BaseDto implements NezasaComponentDtoContract
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

    /**
     * Get the unique identifier for the component.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the type of the component.
     */
    public function getType(): ComponentEnum
    {
        return ComponentEnum::Accommodation;
    }
}
