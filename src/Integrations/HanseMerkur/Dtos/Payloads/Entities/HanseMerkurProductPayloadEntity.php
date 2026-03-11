<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class HanseMerkurProductPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurProductPayloadEntity.
     *
     * @param  Collection<int, HanseMerkurAllocationPayloadEntity>  $insuredPersonAllocations
     */
    public function __construct(
        public string $productInstanceId,
        public Collection $insuredPersonAllocations
    ) {}
}
