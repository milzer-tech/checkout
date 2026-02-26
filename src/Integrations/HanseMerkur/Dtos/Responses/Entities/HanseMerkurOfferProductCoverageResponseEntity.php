<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

final class HanseMerkurOfferProductCoverageResponseEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurOfferProductCoverageResponseEntity.
     */
    public function __construct(
        public ?string $title,
        public ?string $description,
    ) {}

}
