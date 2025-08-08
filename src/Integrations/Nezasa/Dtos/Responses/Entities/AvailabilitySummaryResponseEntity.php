<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;

class AvailabilitySummaryResponseEntity extends BaseDto
{
    /**
     * Create a new instance of AvailabilitySummaryResponseEntity.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Availability/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1availability-check/post
     *
     * @note There are more properties in the response, but we only include the ones we need.
     *
     * @param  Collection<int, AvailabilityComponentResponseEntity>  $components
     */
    public function __construct(
        public Collection $components,
        public bool $nonBookable,
        public bool $bookingWindowEnd,
        public ApplyPromoCodeResponse $prices,
        public array $remarks = []
    ) {}
}
