<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class HanseMerkurMetadataPayloadEntity extends BaseDto
{
    /**
     * Information concerning the point of sale at HMR.
     */
    public function __construct(
        public string $requestorId,
        public string $partnerId,
        public ?string $externalPartnerId = null,
    ) {}
}
