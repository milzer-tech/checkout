<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class RoomAllocationResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the RoomAllocationResponseEntity
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/planner-api-v1.html#tag/Itinerary/paths/~1v1~1itineraries~1%7BitineraryId%7D/get
     *
     * @note There are other properties in this entity, but we don't need them for now.
     *
     * @param  Collection<int, int>|array<int, int>  $childAges
     */
    public function __construct(
        public int $adults,
        public Collection|array $childAges = new Collection,
    ) {
        if (is_array($this->childAges)) {
            $this->childAges = new Collection($childAges);
        }
    }
}
