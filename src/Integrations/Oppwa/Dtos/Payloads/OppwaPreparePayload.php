<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;
use Spatie\LaravelData\Attributes\MapOutputName;

class OppwaPreparePayload extends BaseDto
{
    /**
     * Create a new instance of PreparePayload.
     */
    public function __construct(
        public string $amount,
        public string $currency,
        #[MapOutputName('customer.email')]
        public string $customerEmail,
        public string $paymentType,
        public bool $integrity = true,

    ) {}
}
