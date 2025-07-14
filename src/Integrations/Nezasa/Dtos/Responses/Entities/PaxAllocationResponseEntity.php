<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class PaxAllocationResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the PaxAllocationResponseEntity
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/planner-api-v1.html#tag/Itinerary/paths/~1v1~1itineraries~1%7BitineraryId%7D/get
     *
     * @param  Collection<int, RoomAllocationResponseEntity>  $rooms
     */
    public function __construct(
        public Collection $rooms
    ) {}
}
