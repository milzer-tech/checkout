<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;

class RequlatoryInformationResponse extends BaseDto
{
    /**
     * Create a new instance of the RequlatoryInformationResponse
     *
     * @link https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Checkout/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1regulatory-information/get
     */
    public function __construct(
        public ?string $paymentExplainer,

    ) {}
}
