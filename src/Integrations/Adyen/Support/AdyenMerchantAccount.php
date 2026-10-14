<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Adyen\Support;

use Adyen\Client;
use Adyen\Environment;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Adyen\Exceptions\MissingAdyenCredentialsException;
use Nezasa\Checkout\Integrations\Adyen\Http\AdyenHttpClient;

/**
 * The Adyen merchant account and credentials that payments in a currency are processed with.
 *
 * A currency uses its own credentials from "checkout.integrations.adyen.merchant_accounts", filled
 * by the CHECKOUT_ADYEN_{CURRENCY}_* environment variables. A currency without them uses the
 * "default" credentials when "use_default_credentials" is enabled. The two are never mixed.
 */
final readonly class AdyenMerchantAccount
{
    /**
     * The name of the default credentials.
     */
    public const string DEFAULT_CREDENTIALS = 'default';

    /**
     * Create a new instance of AdyenMerchantAccount.
     */
    public function __construct(
        public string $currency,
        // The credentials it uses: the currency they belong to, or "default".
        public string $credentials,
        public string $merchantAccount,
        #[\SensitiveParameter]
        public string $apiKey,
        public string $clientKey,
        // "test", or the region of the live account, e.g. "live" or "live-us".
        public string $environment,
        public ?string $liveUrlPrefix = null,
    ) {}

    /**
     * Get the merchant account to process payments in the currency.
     *
     * @throws MissingAdyenCredentialsException
     */
    public static function forCurrency(string $currency): self
    {
        $currency = strtoupper(trim($currency));

        if (self::clean(self::currencyCredentials($currency)['merchant_account'] ?? null) !== null) {
            return self::forCredentials($currency, $currency);
        }

        if (Config::boolean('checkout.integrations.adyen.use_default_credentials')) {
            return self::forCredentials(self::DEFAULT_CREDENTIALS, $currency);
        }

        throw MissingAdyenCredentialsException::forCurrency($currency);
    }

    /**
     * Get the merchant account of the given credentials, e.g. the ones a payment was created with.
     *
     * @param  string  $credentials  The currency the credentials belong to, or "default".
     *
     * @throws MissingAdyenCredentialsException
     */
    public static function forCredentials(string $credentials, string $currency): self
    {
        $values = $credentials === self::DEFAULT_CREDENTIALS
            ? Config::array('checkout.integrations.adyen.default')
            : self::currencyCredentials($credentials);

        return self::fromCredentials(strtoupper(trim($currency)), $credentials, $values);
    }

    /**
     * Get the client of the Adyen API library for the merchant account.
     */
    public function client(): Client
    {
        $client = new Client;

        $client->setXApiKey($this->apiKey);
        $client->setEnvironment($this->isLive() ? Environment::LIVE : Environment::TEST, $this->liveUrlPrefix);
        $client->setTimeout(60);
        $client->setConnectionTimeout(60);
        $client->setHttpClient(new AdyenHttpClient);

        return $client;
    }

    /**
     * Check if the merchant account processes live payments.
     */
    public function isLive(): bool
    {
        return $this->environment !== Environment::TEST;
    }

    /**
     * Get the configured credentials of a currency.
     *
     * @return array<array-key, mixed>
     */
    private static function currencyCredentials(string $currency): array
    {
        $accounts = array_change_key_case(Config::array('checkout.integrations.adyen.merchant_accounts'), CASE_UPPER);
        $credentials = $accounts[strtoupper(trim($currency))] ?? [];

        return is_array($credentials) ? $credentials : [];
    }

    /**
     * Create the merchant account from credentials, which must have all their values.
     *
     * @param  array<array-key, mixed>  $values
     *
     * @throws MissingAdyenCredentialsException
     */
    private static function fromCredentials(string $currency, string $credentials, #[\SensitiveParameter] array $values): self
    {
        $merchantAccount = self::clean($values['merchant_account'] ?? null);
        $apiKey = self::clean($values['api_key'] ?? null);
        $clientKey = self::clean($values['client_key'] ?? null);

        if ($merchantAccount === null || $apiKey === null || $clientKey === null) {
            throw $merchantAccount === null && $credentials !== self::DEFAULT_CREDENTIALS
                ? MissingAdyenCredentialsException::forCurrency($credentials)
                : MissingAdyenCredentialsException::incomplete($credentials);
        }

        return new self(
            currency: $currency,
            credentials: $credentials,
            merchantAccount: $merchantAccount,
            apiKey: $apiKey,
            clientKey: $clientKey,
            environment: self::clean($values['environment'] ?? null)
                ?? self::clean(Config::get('checkout.integrations.adyen.environment'))
                ?? Environment::TEST,
            liveUrlPrefix: self::clean($values['live_url_prefix'] ?? null)
                ?? self::clean(Config::get('checkout.integrations.adyen.live_url_prefix')),
        );
    }

    /**
     * Get a configured value without the whitespace and invisible characters around it, which a copied
     * key often brings along, e.g. a line separator or a zero-width space. Adyen rejects a key with them.
     */
    private static function clean(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = preg_replace('/^[\s\p{Z}\p{Cf}]+|[\s\p{Z}\p{Cf}]+$/u', '', $value) ?? $value;

        return $value === '' ? null : $value;
    }
}
