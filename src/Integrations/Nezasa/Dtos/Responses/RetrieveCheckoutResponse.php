<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;

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
        public ApplyPromoCodeResponse $prices,
    ) {}
}
