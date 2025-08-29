<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\LegConnectionEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ModulesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxDetailEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PriceInfoEntity;

class GetItineraryResponse extends BaseDto
{
    /**
     * Create a new instance of the GetItineraryResponseDto
     *
     * @param  Collection<int, ModulesResponseEntity>  $modules
     * @param  Collection<int, LegConnectionEntity>  $startConnections
     * @param  Collection<int, LegConnectionEntity>  $returnConnections
     * @param  Collection<int, PaxDetailEntity>  $paxDetails
     *
     * @link https://support.nezasa.com/hc/en-gb/articles/4404075693969-Planner-API
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     */
    public function __construct(
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
        public string $title,
        public PaxAllocationResponseEntity $allocatedPax,
        public PriceInfoEntity $priceInfo,
        public Collection $modules = new Collection,
        public Collection $paxDetails = new Collection,
        public Collection $startConnections = new Collection,
        public Collection $returnConnections = new Collection,
    ) {}

    /**
     * Count the number of adults in the itinerary.
     */
    public function countAdults(): int
    {
        return $this->paxDetails
            ->reject(fn (PaxDetailEntity $pax): bool => $pax->age && $pax->age < 18)
            ->count();
    }

    /**
     * Count the number of adults in the itinerary.
     */
    public function countChildren(): int
    {
        return $this->paxDetails
            ->filter(fn (PaxDetailEntity $pax): bool => $pax->age && $pax->age < 18)
            ->count();
    }

    /**
     * Get the ages of the children in the itinerary.
     *
     * @return Collection<int, int>
     */
    public function getChildrenAges(): Collection
    {
        return $this->paxDetails
            ->filter(fn (PaxDetailEntity $detail): bool => $detail->age < 18)
            ->pluck('age')
            ->filter()
            ->values();
    }
}
