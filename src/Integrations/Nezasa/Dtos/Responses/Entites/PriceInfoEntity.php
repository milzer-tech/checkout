<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites;

use Nezasa\Checkout\Dtos\BaseDto;

class PriceInfoEntity extends BaseDto
{
    /**
     * Create a new instance of PriceInfoEntity.
     */
    public function __construct(
        public PriceEntity $packagePrice,
    ) {}
}
