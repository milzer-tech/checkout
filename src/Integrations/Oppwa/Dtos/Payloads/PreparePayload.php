<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;

class PreparePayload extends BaseDto
{
    /**
     * Create a new instance of PreparePayload.
     */
    public function __construct(
        public string $amount,
        public string $currency,
        public string $paymentType = 'DB',
        public bool $integrity = true,
        //        public ?string $entityId = null,
    ) {}
}
