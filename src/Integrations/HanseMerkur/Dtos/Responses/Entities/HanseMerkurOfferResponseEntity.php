<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurMoneyEntity;

final class HanseMerkurOfferResponseEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurOfferResponseEntity.
     *
     * @param  Collection<int, HanseMerkurOfferProductResponseEntity>  $products
     */
    public function __construct(
        public Collection $products = new Collection,
        public ?HanseMerkurMoneyEntity $offerTotalPremium = null,
        public ?string $insuranceCompany = null,

    ) {}

}
