<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurDocumentTypeEnum;

final class HanseMerkurDocumentResponseEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurOfferProductCoverageResponseEntity.
     */
    public function __construct(
        public HanseMerkurDocumentTypeEnum $documentType,
        public ?string $url,
    ) {}

}
