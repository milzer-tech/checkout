<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class UrlPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of UrlPayloadEntity.
     *
     * @link https://app.swaggerhub.com/apis-docs/Computop/Paygate_REST_API/1#/hostedPaymentPageSpecificObject
     */
    public function __construct(
        public string $success,
        public string $failure,
        public string $cancel
    ) {}

}
