<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Adyen;

use Adyen\Model\Checkout\Amount;
use Nezasa\Checkout\Dtos\BaseDto;

/**
 * The data of the Adyen payment session that is stored on the transaction.
 */
final class AdyenPaymentData extends BaseDto
{
    /**
     * Create a new instance of AdyenPaymentData.
     */
    public function __construct(
        public string $sessionId,
        // The encoded session data the Drop-in needs to make the payment.
        public string $sessionData,
        // The credentials the payment is processed with: the currency they belong to, or "default".
        public string $credentials,
        // The merchant account that authorizes, captures and reverses the payment.
        public string $merchantAccount,
        // The reference of the payment at Adyen, which is the transaction ID.
        public string $reference,
        // The amount in minor units of the currency, e.g. cents for EUR.
        public int $value,
        public string $currency,
        public string $locale,
        public ?string $countryCode = null,
    ) {}

    /**
     * Get the amount of the payment.
     */
    public function amount(): Amount
    {
        return new Amount(['value' => $this->value, 'currency' => $this->currency]);
    }
}
