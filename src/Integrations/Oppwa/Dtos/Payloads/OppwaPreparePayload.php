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
        #[MapOutputName('customer.givenName')]
        public string $customerGivenName,
        #[MapOutputName('customer.surname')]
        public string $customerSurname,
        #[MapOutputName('billing.street1')]
        public string $billingStreet1,
        #[MapOutputName('billing.city')]
        public string $billingCity,
        #[MapOutputName('billing.postcode')]
        public ?string $billingPostcode,
        #[MapOutputName('billing.country')]
        public string $billingCountry,
        public string $paymentType = 'DB',
        public bool $integrity = true,

    ) {}
}
