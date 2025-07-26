<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class PromoCodeResponseEntity extends BaseDto
{
    /**
     * Create a new instance of PromoCodeResponseEntity.
     */
    public function __construct(
        public string $code,
    ) {}
}
