<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\LegConnectionEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites\ModulesResponseEntity;
use Spatie\LaravelData\Data;

class GetItineraryResponse extends Data
{
    /**
     * Create a new instance of the GetItineraryResponseDto
     *
     * @param  Collection<int, ModulesResponseEntity>  $modules
     * @param  Collection<int, LegConnectionEntity>  $startConnections
     * @param  Collection<int, LegConnectionEntity>  $returnConnections
     *
     * @link https://support.nezasa.com/hc/en-gb/articles/4404075693969-Planner-API
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     */
    public function __construct(
        public Collection $modules,
        public Collection $startConnections,
        public Collection $returnConnections,
    ) {}
}
