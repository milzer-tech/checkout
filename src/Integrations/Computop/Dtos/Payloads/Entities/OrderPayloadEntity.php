<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class OrderPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of OrderPayloadEntity.
     *
     * @param  array<int, string>  $description
     *
     * @link https://app.swaggerhub.com/apis-docs/Computop/Paygate_REST_API/1#/Payments
     */
    public function __construct(
        public string $id,
        public array $description
    ) {}

}
