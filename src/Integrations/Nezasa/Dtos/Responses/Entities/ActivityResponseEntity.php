<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class ActivityResponseEntity extends Data
{
    /**
     * Create a new instance of the ActivityResponseEntity
     *
     * @link https://support.nezasa.com/hc/en-gb/articles/4404075693969-Planner-API
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     */
    public function __construct(
        public string $name,
        public CarbonImmutable $startDateTime,
        public CarbonImmutable $endDateTime,
    ) {}
}
