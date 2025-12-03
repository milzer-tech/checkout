<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TermsAndConditionsResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\BookingStateEnum;

class RetrieveCheckoutResponse extends BaseDto
{
    /**
     * Create a new instance of the RetrieveCheckoutResponse
     *
     * @link https://support.nezasa.com/hc/en-gb/articles/29588280597265-Checkout-API
     *
     * @note There are other properties in the response, but we are only interested in the modules for now.
     */
    public function __construct(
        public BookingStateEnum $checkoutState,
        public ApplyPromoCodeResponse $prices,
        public TermsAndConditionsResponseEntity $termsAndConditions = new TermsAndConditionsResponseEntity,
    ) {}
}
