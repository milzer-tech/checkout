<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\AvailabilitySummaryResponseEntity;

class VerifyAvailabilityResponse extends BaseDto
{
    /**
     * Create a new instance of VerifyAvailabilityResponse.
     *
     * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Availability/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1availability-check/post
     *
     * @note There are more properties in the response, but we only include the ones we need.
     */
    public function __construct(public AvailabilitySummaryResponseEntity $summary) {}
}
