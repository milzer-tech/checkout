<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class EuPrrlLinkResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the EuPrrlLinkResponseEntity.
     */
    public function __construct(
        public string $url,
        public string $linkText,
    ) {}
}
