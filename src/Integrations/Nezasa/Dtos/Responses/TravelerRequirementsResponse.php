<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\BillingRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ContactRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PassengerRequirementEntity;

class TravelerRequirementsResponse extends BaseDto
{
    /**
     * Create a new instance of the TravelerRequirementsResponse
     *
     * @link https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Traveler-Information/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1required-traveler-details/get
     */
    public function __construct(
        public ContactRequirementEntity $contact,
        public BillingRequirementEntity $billing,
        public PassengerRequirementEntity $passenger,
    ) {}
}
