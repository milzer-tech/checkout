<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurCoveredEventPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurInsuredPersonPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurMetadataPayloadEntity;

class HanseMerkurCreateOffersPayload extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurOffersPayload.
     *
     * @param  Collection<int, HanseMerkurInsuredPersonPayloadEntity>  $insuredPersons
     */
    public function __construct(
        public HanseMerkurMetadataPayloadEntity $metadata,
        public HanseMerkurCoveredEventPayloadEntity $coveredEvent,
        public Collection $insuredPersons,
    ) {}
}
