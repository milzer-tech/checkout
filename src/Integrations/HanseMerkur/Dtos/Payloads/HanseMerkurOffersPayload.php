<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurCoveredEventPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurInsuredPersonPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurMetadataPayloadEntity;

class HanseMerkurOffersPayload extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurOffersPayload.
     *
     * @param  array<int, HanseMerkurInsuredPersonPayloadEntity>  $insuredPersons
     */
    public function __construct(
        public HanseMerkurMetadataPayloadEntity $metadata,
        public HanseMerkurCoveredEventPayloadEntity $coveredEvent,
        public array $insuredPersons,
    ) {}
}
