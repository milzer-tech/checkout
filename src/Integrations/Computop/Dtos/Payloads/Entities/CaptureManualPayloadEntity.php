<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class CaptureManualPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of CaptureManualPayloadEntity
     *
     * @link https://app.swaggerhub.com/apis-docs/Computop/Paygate_REST_API/1#/Payments/createPayment
     */
    public function __construct(public string $final = 'yes') {}

}
