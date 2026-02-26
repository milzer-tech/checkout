<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurCoveredEventPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurInsuredPersonPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities\HanseMerkurOfferResponseEntity;

final class HanseMerkurCreateOffersResponse extends BaseDto
{
    /**
     * Create a new instance of HanseMerkurCreateOffersResponse.
     *
     * @param  Collection<int, HanseMerkurInsuredPersonPayloadEntity>  $insuredPersons
     * @param  Collection<int, HanseMerkurOfferResponseEntity>  $offers
     */
    public function __construct(
        public HanseMerkurCoveredEventPayloadEntity $coveredEvent,
        public Collection $insuredPersons,
        public ?string $locale = null,
        public Collection $offers = new Collection,
    ) {}

}
