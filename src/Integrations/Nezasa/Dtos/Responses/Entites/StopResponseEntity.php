<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class StopResponseEntity extends Data
{
    /**
     * Create a new instance of the StopResponseEntityDto
     *
     * @param  Collection<int, AccommodationResponseEntity>  $accommodations
     * @param  Collection<int, ActivityResponseEntity>  $activities
     *
     * @link https://support.nezasa.com/hc/en-gb/articles/4404075693969-Planner-API
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     */
    public function __construct(
        public int $nights,
        public Collection $accommodations = new Collection,
        public Collection $activities = new Collection,
    ) {}
}
