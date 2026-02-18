<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Illuminate\Support\Uri;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Transaction;

class PaymentPrepareData extends BaseDto
{
    /**
     * Create a new instance of PaymentPrepareData.
     */
    public function __construct(
        public Transaction $transaction,
        // this the payment return URL
        public Uri $returnUrl,
        // this is the home page URL
        public Uri $cancelUrl,
        public ContactInfoPayloadEntity $contact,
        public Price $price,
        public string $checkoutId,
        public string $itineraryId,
        public ?string $origin = null,
        public ?string $lang = null,
    ) {}
}
