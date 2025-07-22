<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Enums\TravelerRequirementFieldEnum;
use Spatie\LaravelData\Attributes\MapInputName;

class ContactRequirementEntity extends BaseDto
{
    /**
     * Create a new instance of the ContactRequirementEntity
     *
     * @link https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Traveler-Information/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1required-traveler-details/get
     */
    public function __construct(
        public TravelerRequirementFieldEnum $firstName,
        public TravelerRequirementFieldEnum $lastName,
        public TravelerRequirementFieldEnum $companyName,
        public TravelerRequirementFieldEnum $email,
        public TravelerRequirementFieldEnum $mobilePhone,
        #[MapInputName('address1')]
        public TravelerRequirementFieldEnum $street1,
        #[MapInputName('address2')]
        public TravelerRequirementFieldEnum $street2,
        public TravelerRequirementFieldEnum $postalCode,
        public TravelerRequirementFieldEnum $city,
        public TravelerRequirementFieldEnum $country,
        public TravelerRequirementFieldEnum $state,
        public TravelerRequirementFieldEnum $gender,
        public TravelerRequirementFieldEnum $taxNumber,
        public TravelerRequirementFieldEnum $localIdNumber
    ) {}
}
