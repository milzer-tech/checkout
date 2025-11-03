<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class ExternallyPaidChargesResponseEntity extends BaseDto
{
    /**
     * Create a new instance of ExternallyPaidChargeResponseEntity.
     *
     * @param Collection<int, ExternallyPaidChargeResponseEntity>
     */
    public function __construct(
        public Price $totalPrice,
        public Collection|array $externallyPaidCharges = new Collection,
    ) {
        if (is_array($this->externallyPaidCharges)) {
            $this->externallyPaidCharges = new Collection($this->externallyPaidCharges);
        }
    }
}
