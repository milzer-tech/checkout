<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Dtos\Contracts\NezasaComponentDtoContract;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\ComponentEnum;

class ItineraryRentalCar extends BaseDto implements NezasaComponentDtoContract
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
        return ComponentEnum::RentalCar;
    }
}
