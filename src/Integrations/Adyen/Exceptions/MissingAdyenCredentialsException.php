<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Adyen\Exceptions;

use Nezasa\Checkout\Integrations\Adyen\Support\AdyenMerchantAccount;
use RuntimeException;

class MissingAdyenCredentialsException extends RuntimeException
{
    /**
     * The currency has no credentials, and the default credentials are not used.
     */
    public static function forCurrency(string $currency): self
    {
        return new self("No Adyen credentials are configured for $currency or enable using the default credentials.");
    }

    /**
     * The credentials do not have all their values.
     *
     * @param  string  $credentials  The currency of the credentials, or "default".
     */
    public static function incomplete(string $credentials): self
    {
        return new self($credentials === AdyenMerchantAccount::DEFAULT_CREDENTIALS
            ? 'The default Adyen credentials are incomplete.'
            : "The Adyen credentials of $credentials are incomplete.");
    }
}
