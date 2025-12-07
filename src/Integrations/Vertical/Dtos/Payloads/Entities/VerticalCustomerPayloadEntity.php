<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class VerticalCustomerPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of VerticalCustomerPayloadEntity.
     */
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email_address,
    ) {}

}
