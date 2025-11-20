<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ExternallyPaidChargesResponseEntity extends BaseDto
{
    /**
     * Create a new instance of ExternallyPaidChargeResponseEntity.
     *
     * @param  Collection<int, ExternallyPaidChargeResponseEntity>  $externallyPaidCharges
     */
    public function __construct(
        public Price $totalPrice,
        #[DataCollectionOf(ExternallyPaidChargeResponseEntity::class)]
        public Collection $externallyPaidCharges = new Collection,
    ) {
        //        if (is_array($this->externallyPaidCharges)) {
        //            $this->externallyPaidCharges = new Collection($this->externallyPaidCharges);
        //        }
    }
}
