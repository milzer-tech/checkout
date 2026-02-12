<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads;

use Nezasa\Checkout\Dtos\BaseDto;

class OppwaComplationPayload extends BaseDto
{
    /**
     * Create a new instance of PreparePayload.
     *
     * @see https://axcessms.docs.oppwa.com/integrations/backoffice
     */
    public function __construct(
        public string $amount,
        public string $currency,
        public string $paymentType, // CP tp Capture and RV to reverse the pre-authorization
    ) {}
}
