<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ModulesResponseEntity extends Data
{
    /**
     * Create a new instance of the ModulesResponseEntityDto
     *
     * @param  Collection<int, LegResponseEntity>  $legs
     * @param  Collection<int, LegConnectionEntity>  $returnConnections
     *
     * @link https://support.nezasa.com/hc/en-gb/articles/4404075693969-Planner-API
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     */
    public function __construct(
        public Collection $legs,
        public LocationResponseEntity $startLocation,
        public LocationResponseEntity $endLocation,
        public Collection $returnConnections = new Collection,
    ) {}
}
