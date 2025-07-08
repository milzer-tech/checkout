<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class LegConnectionEntity extends Data
{
    /**
     * Create a new instance of the LegConnectionEntity
     */
    public function __construct(
        public string $name,
        public string $connectionType,
        public LocationResponseEntity $startLocation,
        public LocationResponseEntity $endLocation,
        public CarbonImmutable $startDateTime,
        public CarbonImmutable $endDateTime,
        public bool $isPlaceholder,
    ) {}
}
