<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entites;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class PriceInfoEntity extends BaseDto
{
    /**
     * Create a new instance of PriceInfoEntity.
     */
    public function __construct(
        public Price $packagePrice,
    ) {}
}
