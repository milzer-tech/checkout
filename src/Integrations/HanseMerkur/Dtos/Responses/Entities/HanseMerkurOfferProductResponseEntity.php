<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurMoneyEntity;

final class HanseMerkurOfferProductResponseEntity extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurOfferProductResponseEntity.
     *
     * @param  Collection<int, HanseMerkurOfferProductCoverageResponseEntity>  $coverageData
     * @param  Collection<int, HanseMerkurDocumentResponseEntity>  $documents
     */
    public function __construct(
        public string $productId,
        public string $productInstanceId,
        public HanseMerkurMoneyEntity $productTotalPremium,
        public string $title,
        public Collection $coverageData = new Collection,
        public Collection $documents = new Collection,

    ) {}

}
